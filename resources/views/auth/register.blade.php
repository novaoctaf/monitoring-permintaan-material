@extends('layouts.guest')

@section('title', 'Register')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Daftar untuk membuat akun baru</p>
@endsection

@section('content')
<form method="POST" action="{{ route('register') }}" class="mt-3">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label fw-medium">{{ __('Nama Lengkap') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('name') border-danger text-danger @enderror">
                <i class="bi bi-person"></i>
            </span>
            <input id="name" type="text" class="form-control border-start-0 @error('name') is-invalid @enderror"
                name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autocomplete="name"
                autofocus>
        </div>
        @error('name')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label fw-medium">{{ __('Alamat Email') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('email') border-danger text-danger @enderror">
                <i class="bi bi-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" placeholder="Masukkan alamat email" required
                autocomplete="email">
        </div>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-medium">{{ __('Kata Sandi') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('password') border-danger text-danger @enderror">
                <i class="bi bi-lock"></i>
            </span>
            <input id="password" type="password"
                class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required
                placeholder="Masukkan kata sandi" autocomplete="new-password">
        </div>
        @error('password')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label fw-medium">{{ __('Konfirmasi Kata Sandi') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-lock-fill"></i>
            </span>
            <input id="password-confirm" type="password" class="form-control border-start-0"
                name="password_confirmation" required placeholder="Masukkan ulang kata sandi"
                autocomplete="new-password">
        </div>
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary py-2 fw-medium">
            {{ __('Daftar') }}
        </button>
    </div>

    <div class="text-center">
        <p class="mb-0">Sudah memiliki akun?
            <a href="{{ route('login') }}" class="text-decoration-none fw-medium">Masuk</a>
        </p>
    </div>
</form>
@endsection