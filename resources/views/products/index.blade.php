@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary fw-bold">📦 Inventory Dashboard</h2>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger fw-semibold">
            ⏻ Log out
        </button>
    </form>
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
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-primary border-5">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small font-weight-bold">Total Products</h6>
                <h2 class="fw-bold mb-0 text-dark">{{ $totalProducts }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-danger border-5">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small font-weight-bold">Low Stock Alerts</h6>
                <h2 class="fw-bold mb-0 text-danger">{{ $lowStockCount }}</h2>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('products.create') }}" class="btn btn-primary fw-semibold">
        ➕ Add New Product
    </a>
    <a href="{{ route('products.summary.pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-outline-secondary fw-semibold">
        📥 Download PDF Summary
    </a>
</div>
@if($lowStocks->count() > 0)
<div class="alert alert-danger shadow-sm mb-4">
    <h5 class="alert-heading fw-bold">⚠️ Critical Low Stock Warnings</h5>
    <hr class="my-2">
    <ul class="mb-0 pl-3">
        @foreach($lowStocks as $item)
            <li><strong>{{ $item->product_name }}</strong> is down to only <span class="badge bg-danger">{{ $item->stock }}</span> items (Minimum Threshold: {{ $item->minimum_stock }}).</li>
        @endforeach
    </ul>
</div>

@endif
<div class="card shadow-sm border-0 p-3">
    <h5 class="card-title mb-3 fw-bold text-secondary">Current Inventory Ledger</h5>
    <div class="mb-3">
        <form id="filters-form" class="row g-2">
            <div class="col-md-6">
                <input type="search" name="q" id="search-box" value="{{ request('q') }}" class="form-control" placeholder="Search products or categories...">
            </div>
            <div class="col-md-3">
                <select name="category" id="category-filter" class="form-select">
                    <option value="">All Categories</option>
                    @if(isset($categories))
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category')==$cat)>{{ $cat }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" id="sort-filter" class="form-select">
                    <option value="">Sort (default)</option>
                    <option value="stock_asc" @selected(request('sort')=='stock_asc')>Stock ↑</option>
                    <option value="stock_desc" @selected(request('sort')=='stock_desc')>Stock ↓</option>
                    <option value="name_asc" @selected(request('sort')=='name_asc')>Name A→Z</option>
                    <option value="name_desc" @selected(request('sort')=='name_desc')>Name Z→A</option>
                </select>
            </div>
        </form>
    </div>

    <div id="products-table-container">
        @include('products._table')
    </div>

    <script>
        (function(){
            const form = document.getElementById('filters-form');
            const search = document.getElementById('search-box');
            const category = document.getElementById('category-filter');
            const sort = document.getElementById('sort-filter');
            const container = document.getElementById('products-table-container');

            let debounceTimer = null;
            function fetchResults(url){
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        container.innerHTML = html;
                        // update browser url
                        history.replaceState({}, '', url);
                    }).catch(()=>{});
            }

            function submitFilters(){
                const params = new URLSearchParams(new FormData(form));
                const url = window.location.pathname + '?' + params.toString();
                fetchResults(url);
            }

            search.addEventListener('input', function(){
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitFilters, 350);
            });

            category.addEventListener('change', submitFilters);
            sort.addEventListener('change', submitFilters);

            // Delegate pagination link clicks inside container
            container.addEventListener('click', function(e){
                const a = e.target.closest('a');
                if (!a) return;
                const href = a.getAttribute('href');
                if (!href) return;
                // If link is a pagination link, fetch via AJAX
                if (href.indexOf('?') !== -1 || href.match(/page=\d+/)) {
                    e.preventDefault();
                    fetchResults(href);
                }
            });
        })();
    </script>
</div>
<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <form id="adjust-stock-form" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="adjust-product-name" class="fw-bold"></p>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="notes" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
        var adjustModal = document.getElementById('adjustStockModal');
        adjustModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var form = document.getElementById('adjust-stock-form');
                form.action = '/products/' + id + '/adjust';
                document.getElementById('adjust-product-name').textContent = name;
        });
});
</script>
@endsection
