<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Stock Level</th>
                <th>Min Level</th>
                <th>Price</th>
                <th>System Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                   <td class="text-muted fw-bold">
                        {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                    <td class="fw-bold text-dark">{{ $product->product_name }}</td>
                    <td><span class="badge bg-secondary text-light">{{ $product->category }}</span></td>
                    <td>{{ $product->stock }} units</td>
                    <td class="text-muted">{{ $product->minimum_stock }}</td>
                    <td class="fw-semibold">₱{{ number_format($product->price, 2) }}</td>
                    <td>
                        @if($product->stock <= $product->minimum_stock)
                            <span class="badge bg-danger text-white px-2 py-1">Critical Low Stock</span>
                        @else
                            <span class="badge bg-success text-white px-2 py-1">Optimal Stock</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning me-2" title="Edit">✏️ Edit</a>
                        <button class="btn btn-sm btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#adjustStockModal" data-id="{{ $product->id }}" data-name="{{ $product->product_name }}" data-stock="{{ $product->stock }}">📈 Adjust Stock</button>
                        <a href="{{ route('products.logs', $product->id) }}" class="btn btn-sm btn-info me-2" title="Logs">📜 Logs</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">🗑️ Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No records found. Add item products to fill the inventory ledger.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $products->links() }}
</div>
