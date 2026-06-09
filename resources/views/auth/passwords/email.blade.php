@extends('layouts.guest')

@section('title', 'Reset Password')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Reset kata sandi Anda</p>
@endsection

@section('content')
@if (session('status'))
<div class="alert alert-success mb-4" role="alert">
    {{ session('status') }}
</div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="mt-3">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label fw-medium">{{ __('Alamat Email') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('email') border-danger text-danger @enderror">
                <i class="bi bi-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" placeholder="Masukkan email anda" required autocomplete="email" autofocus>
        </div>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary py-2 fw-medium">
            {{ __('Kirim Tautan Reset Kata Sandi') }}
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> {{ __('Kembali ke halaman login') }}
        </a>
    </div>
</form>
@endsection
