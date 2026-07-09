@extends('layouts.app')
@section('content')
<div class="container" style="max-width:420px;margin-top:40px">
    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Sign in</h4>
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('register') }}" class="btn btn-link">Create an account</a>
                <button class="btn btn-primary">Login</button>
            </div>
        </form>
     </div>
 </div>
@endsection
