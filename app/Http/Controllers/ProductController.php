<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Product::query();

        // Search across multiple columns
        $q = request()->query('q');
        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('product_name', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('price', 'like', "%{$q}%");
            });
        }

        // Category filter
        $category = request()->query('category');
        if ($category) {
            $query->where('category', $category);
        }

        // Sorting (default by id)
        $sort = request()->query('sort');
        if ($sort === 'stock_asc') {
            $query->orderBy('stock', 'asc');
        } elseif ($sort === 'stock_desc') {
            $query->orderBy('stock', 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('product_name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('product_name', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        // Paginate results (10 per page)
        $products = $query->paginate(10)->withQueryString();

        // Dashboard / stats
        $lowStocks = Product::whereColumn('stock', '<=', 'minimum_stock')->get();
        $totalProducts = Product::count();
        $lowStockCount = $lowStocks->count();

        // Categories for filter dropdown
        $categories = Product::distinct()->pluck('category');

        // Return partial for AJAX requests to support live updates
        if (request()->ajax()) {
            return response()->view('products._table', compact('products'));
        }

        return view('products.index', compact(
            'products',
            'lowStocks',
            'totalProducts',
            'lowStockCount',
            'categories'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);
        $data = $request->all();
        $product = Product::create($data);

        // Log initial stock as a Stock In transaction when created with stock > 0
        if (isset($data['stock']) && intval($data['stock']) > 0) {
            TransactionLog::create([
                'product_id' => $product->id,
                'change' => intval($data['stock']),
                'type' => 'in',
                'previous_stock' => 0,
                'new_stock' => intval($data['stock']),
                'user_id' => auth()->id() ?? null,
                'notes' => 'Initial stock on product creation',
            ]);
        }

        // Notify management if stock is at or below minimum
        if ($product->stock <= $product->minimum_stock) {
            $emails = array_map('trim', explode(',', env('MANAGEMENT_EMAILS', 'manager@example.com')));
            foreach ($emails as $email) {
                Notification::route('mail', $email)->notify(new LowStockAlert($product));
            }
        }

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
    $request->validate([
        'product_name' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'stock' => 'required|integer|min:0',
        'minimum_stock' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0'
    ]);

    $originalStock = $product->stock;
    $data = $request->all();

    $product->update($data);

    // If stock changed, create a transaction log
    if (isset($data['stock']) && intval($data['stock']) !== intval($originalStock)) {
        $newStock = intval($data['stock']);
        $change = $newStock - intval($originalStock);
        TransactionLog::create([
            'product_id' => $product->id,
            'change' => $change,
            'type' => $change > 0 ? 'in' : 'out',
            'previous_stock' => intval($originalStock),
            'new_stock' => $newStock,
            'user_id' => auth()->id() ?? null,
            'notes' => 'Stock adjusted during product update',
        ]);
    }

    return redirect()->route('products.index')
                     ->with('success', 'Product updated successfully.');
    }

    /**
     * Generate a downloadable CSV summary of the current inventory.
     */
    public function summaryPdf(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $query->where(function($w) use ($request) {
                $q = $request->query('q');
                $w->where('product_name', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('price', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->query('sort') === 'stock_asc') {
            $query->orderBy('stock', 'asc');
        } elseif ($request->query('sort') === 'stock_desc') {
            $query->orderBy('stock', 'desc');
        } elseif ($request->query('sort') === 'name_asc') {
            $query->orderBy('product_name', 'asc');
        } elseif ($request->query('sort') === 'name_desc') {
            $query->orderBy('product_name', 'desc');
        }

        $products = $query->get();

        // Try to render PDF using Dompdf if available
        $html = view('products.summary_pdf', compact('products'))->render();

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="inventory-summary.pdf"',
            ]);
        }

        // Fallback to CSV if Dompdf not installed
        $csvContent = "ID,Product Name,Category,Stock,Minimum Stock,Price,Status\n";
        foreach ($products as $product) {
            $status = $product->stock <= $product->minimum_stock ? 'Low Stock' : 'In Stock';
            $csvContent .= sprintf(
                '%d,"%s","%s",%d,%d,%.2f,%s' . "\n",
                $product->id,
                str_replace('"', '""', $product->product_name),
                str_replace('"', '""', $product->category),
                $product->stock,
                $product->minimum_stock,
                $product->price,
                $status
            );
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-summary.csv"',
        ]);
    }

    /**
     * Export inventory as an Excel-friendly CSV (works well with Excel).
     */
    public function summaryExcel(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $query->where(function($w) use ($request) {
                $q = $request->query('q');
                $w->where('product_name', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('price', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $products = $query->get();

        // If PhpSpreadsheet is available, generate an .xlsx file
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['ID','Product Name','Category','Stock','Minimum Stock','Price','Status'], null, 'A1');
            $row = 2;
            foreach ($products as $product) {
                $status = $product->stock <= $product->minimum_stock ? 'Low Stock' : 'In Stock';
                $sheet->fromArray([
                    $product->id,
                    $product->product_name,
                    $product->category,
                    $product->stock,
                    $product->minimum_stock,
                    number_format($product->price, 2, '.', ''),
                    $status
                ], null, 'A' . $row);
                $row++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $tempFile = sys_get_temp_dir() . '/inventory-summary.xlsx';
            $writer->save($tempFile);
            return response()->download($tempFile, 'inventory-summary.xlsx')->deleteFileAfterSend(true);
        }

        // Fallback to Excel-friendly CSV
        $csvContent = "ID,Product Name,Category,Stock,Minimum Stock,Price,Status\n";
        foreach ($products as $product) {
            $status = $product->stock <= $product->minimum_stock ? 'Low Stock' : 'In Stock';
            $csvContent .= sprintf(
                '%d,"%s","%s",%d,%d,%.2f,%s' . "\n",
                $product->id,
                str_replace('"', '""', $product->product_name),
                str_replace('"', '""', $product->category),
                $product->stock,
                $product->minimum_stock,
                $product->price,
                $status
            );
        }

        return response($csvContent, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="inventory-summary.xls"',
        ]);
    }

    /**
     * Adjust stock for a product (Stock In / Stock Out) and log the transaction.
     */
    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $amount = intval($request->input('amount'));
        $type = $request->input('type');
        $previous = $product->stock;

        if ($type === 'in') {
            $new = $previous + $amount;
            $change = $amount;
        } else {
            $new = max(0, $previous - $amount);
            $change = $new - $previous; // negative or zero
        }

        $product->update(['stock' => $new]);

        TransactionLog::create([
            'product_id' => $product->id,
            'change' => $change,
            'type' => $type,
            'previous_stock' => $previous,
            'new_stock' => $new,
            'user_id' => auth()->id() ?? null,
            'notes' => $request->input('notes'),
        ]);

        // Notify management if stock is at or below minimum after adjustment
        if ($product->stock <= $product->minimum_stock) {
            $emails = array_map('trim', explode(',', env('MANAGEMENT_EMAILS', 'manager@example.com')));
            foreach ($emails as $email) {
                Notification::route('mail', $email)->notify(new LowStockAlert($product));
            }
        }

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }

    /**
     * Show transaction logs for a product.
     */
    public function logs(Product $product)
    {
        $logs = $product->transactionLogs()->latest()->paginate(20);
        return view('products.logs', compact('product', 'logs'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product removed successfully.');
    }
}
