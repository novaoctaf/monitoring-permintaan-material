@extends('layouts.guest')

@section('title', 'Verifikasi Email')

@section('card-header')
<h3 class="fw-bold text-primary mb-0">Sistem Monitoring</h3>
<p class="text-muted mt-1 mb-0">Verifikasi alamat email Anda</p>
@endsection

@section('content')
<div class="text-center mb-4">
    <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
</div>

<div class="alert alert-info" role="alert">
    {{ __('Sebelum melanjutkan, silakan periksa email Anda untuk tautan verifikasi.') }}
</div>

<p>{{ __('Jika Anda tidak menerima email') }}</p>

<form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
    @csrf
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-outline-primary py-2">{{ __('Kirim ulang') }}</button>
    </div>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> {{ __('Kembali ke halaman login') }}
    </a>
</div>

@if (session('resent'))
<div class="alert alert-success mt-3" role="alert">
    {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
</div>
@endif
@endsection