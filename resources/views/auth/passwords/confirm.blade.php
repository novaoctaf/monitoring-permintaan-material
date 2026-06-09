@extends('layouts.guest')

@section('title', 'Konfirmasi Password')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Konfirmasi kata sandi Anda</p>
@endsection

@section('content')
<div class="text-center mb-3">
    <p>{{ __('Mohon konfirmasi kata sandi Anda sebelum melanjutkan.') }}</p>
</div>

<form method="POST" action="{{ route('password.confirm') }}" class="mt-3">
    @csrf

    <div class="mb-4">
        <label for="password" class="form-label fw-medium">{{ __('Kata Sandi') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('password') border-danger text-danger @enderror">
                <i class="bi bi-lock"></i>
            </span>
            <input id="password" type="password"
                class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required
                placeholder="Masukkan kata sandi" autocomplete="current-password">
        </div>
        @error('password')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="d-grid gap-2 mb-3">
        <button type="submit" class="btn btn-primary py-2 fw-medium">
            {{ __('Konfirmasi Kata Sandi') }}
        </button>
    </div>

    <div class="text-center">
        @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="text-decoration-none">
            {{ __('Lupa Kata Sandi?') }}
        </a>
        @endif
    </div>
</form>
@endsection