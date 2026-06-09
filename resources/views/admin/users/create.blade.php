@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.users.store') }}" method="POST">
      @csrf
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Pengguna</h3>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label required">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
              value="{{ old('name') }}" required autofocus>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
              value="{{ old('email') }}" required>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label required">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label required">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Peran</label>
            <div class="row row-cols-1 row-cols-md-3 g-3">
              @foreach($roles as $role)
                <div class="col">
                  <label class="form-check form-switch">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input" 
                      {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $role->name }}</span>
                  </label>
                </div>
              @endforeach
            </div>
            @error('roles')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-auto">
              <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <button type="reset" class="btn btn-link">Reset</button>
            <button type="submit" class="btn btn-primary ms-2">
              <i class="ti ti-device-floppy"></i> Simpan
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection