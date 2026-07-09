@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary fw-bold">➕ Add New Inventory Product</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">← Back to Inventory</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Success!</strong> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error:</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <div class="card shadow-sm border-0 p-4">
            <h5 class="card-title mb-4 fw-bold text-secondary">Enter Product Details</h5>
            
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="product_name" class="form-label fw-semibold">Product Name</label>
                    <input type="text" id="product_name" name="product_name" class="form-control @error('product_name') is-invalid @enderror" placeholder="e.g., USB Flash Drive" required value="{{ old('product_name') }}">
                    @error('product_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label fw-semibold">Category</label>
                    <input type="text" id="category" name="category" class="form-control @error('category') is-invalid @enderror" placeholder="e.g., Accessories" required value="{{ old('category') }}">
                    @error('category')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stock" class="form-label fw-semibold">Initial Stock Quantity</label>
                            <input type="number" id="stock" name="stock" class="form-control @error('stock') is-invalid @enderror" placeholder="0" required value="{{ old('stock', 0) }}" min="0">
                            @error('stock')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="minimum_stock" class="form-label fw-semibold">Low-Stock Alert Level</label>
                            <input type="number" id="minimum_stock" name="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" placeholder="10" required value="{{ old('minimum_stock', 10) }}" min="0">
                            @error('minimum_stock')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="price" class="form-label fw-semibold">Unit Price (₱)</label>
                    <input type="number" id="price" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" required value="{{ old('price', 0) }}" min="0">
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">Add Product Records</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
