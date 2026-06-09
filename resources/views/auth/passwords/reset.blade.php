@extends('layouts.guest')

@section('title', 'Reset Password')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Atur ulang kata sandi Anda</p>
@endsection

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="mt-3">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label fw-medium">{{ __('Alamat Email') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('email') border-danger text-danger @enderror">
                <i class="bi bi-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                name="email" value="{{ $email ?? old('email') }}" placeholder="Masukkan email anda" required autocomplete="email" autofocus>
        </div>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-medium">{{ __('Kata Sandi Baru') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('password') border-danger text-danger @enderror">
                <i class="bi bi-lock"></i>
            </span>
            <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror"
                name="password" required placeholder="Masukkan kata sandi baru" autocomplete="new-password">
        </div>
        @error('password')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label fw-medium">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-lock-fill"></i>
            </span>
            <input id="password-confirm" type="password" class="form-control border-start-0"
                name="password_confirmation" required placeholder="Masukkan ulang kata sandi baru" autocomplete="new-password">
        </div>
    </div>

    <div class="d-grid gap-2 mb-3">
        <button type="submit" class="btn btn-primary py-2 fw-medium">
            {{ __('Reset Kata Sandi') }}
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> {{ __('Kembali ke halaman login') }}
        </a>
    </div>
</form>
@endsection
