@extends('layouts.guest')

@section('title', 'Login')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Masuk untuk melanjutkan ke dashboard</p>
@endsection

@section('content')
<form method="POST" action="{{ route('login') }}" class="mt-3">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label fw-medium">{{ __('Alamat Email') }}</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 @error('email') border-danger text-danger @enderror">
                <i class="bi bi-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}" placeholder="Masukkan email anda" required autocomplete="email"
                autofocus>
        </div>
        @error('email')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-medium mb-0">{{ __('Kata Sandi') }}</label>
        <div class="input-group mt-1">
            <span class="input-group-text bg-light border-end-0 @error('password') border-danger text-danger @enderror">
                <i class="bi bi-lock"></i>
            </span>
            <input id="password" type="password"
                class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required
                autocomplete="current-password" placeholder="Masukkan kata sandi anda">
        </div>
        @error('password')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                {{ __('Ingat Saya') }}
            </label>
        </div>
    </div>

    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary py-2 fw-medium">
            {{ __('Masuk') }}
        </button>
    </div>

</form>
@endsection