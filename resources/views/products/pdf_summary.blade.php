<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0; color: #555; }
        .stats { width: 100%; margin-bottom: 20px; }
        .stats td { padding: 8px; border: 1px solid #ddd; }
        .low-stock { color: #c00; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; }
        .table th { background: #f5f5f5; }
        .footer { font-size: 10px; color: #666; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Summary</h1>
        <p>Generated on {{ now()->format('F j, Y H:i') }}</p>
    </div>

    <table class="stats">
        <tr>
            <td><strong>Total Products</strong></td>
            <td>{{ $totalProducts }}</td>
        </tr>
        <tr>
            <td><strong>Critical Low Stock Items</strong></td>
            <td class="low-stock">{{ $lowStockCount }}</td>
        </tr>
    </table>

    <h3>Critical Low Stock Products</h3>
    @if($lowStocks->count())
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Minimum Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStocks as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->stock }}</td>
                        <td>{{ $item->minimum_stock }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No critical low stock products at the moment.</p>
    @endif

    <h3>Inventory Details</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Minimum Stock</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->minimum_stock }}</td>
                    <td>₱{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock <= $product->minimum_stock ? 'Critical' : 'Optimal' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Inventory Management System — generated PDF summary
    </div>
</body>
</html>
