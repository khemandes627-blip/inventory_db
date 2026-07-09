@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Transaction Logs — {{ $product->product_name }}</h3>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="card p-3">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Previous</th>
                    <th>New</th>
                    <th>Notes</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ ucfirst($log->type) }}</td>
                        <td>{{ $log->change }}</td>
                        <td>{{ $log->previous_stock }}</td>
                        <td>{{ $log->new_stock }}</td>
                        <td>{{ $log->notes }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No transaction logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
