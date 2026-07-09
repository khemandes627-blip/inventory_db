@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h3>Edit Product</h3>
        </div>

        <div class="card-body">

            <form action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Product Name</label>
                    <input type="text"
                           name="product_name"
                           class="form-control"
                           value="{{ old('product_name', $product->product_name) }}">
                </div>

                <div class="mb-3">
                    <label>Category</label>
                    <input type="text"
                           name="category"
                           class="form-control"
                           value="{{ old('category', $product->category) }}">
                </div>

                <div class="mb-3">
                    <label>Stock</label>
                    <input type="number"
                           name="stock"
                           class="form-control"
                           value="{{ old('stock', $product->stock) }}">
                </div>

                <div class="mb-3">
                    <label>Minimum Stock</label>
                    <input type="number"
                           name="minimum_stock"
                           class="form-control"
                           value="{{ old('minimum_stock', $product->minimum_stock) }}">
                </div>

                <div class="mb-3">
                    <label>Price</label>
                    <input type="number"
                           step="0.01"
                           name="price"
                           class="form-control"
                           value="{{ old('price', $product->price) }}">
                </div>

                <button class="btn btn-success">
                    Update Product
                </button>

                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>
    </div>
</div>
@endsection