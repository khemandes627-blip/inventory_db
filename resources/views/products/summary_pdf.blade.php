<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
    <title>Inventory Summary</title>
</head>
<body>
    <h2>Inventory Summary</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
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
                <td>{{ number_format($product->price, 2) }}</td>
                <td>{{ $product->stock <= $product->minimum_stock ? 'Low Stock' : 'In Stock' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
