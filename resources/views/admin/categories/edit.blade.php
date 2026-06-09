@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Edit Kategori')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
      @csrf
      @method('PUT')
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Form Edit Kategori</h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label required">Nama Kategori</label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                     value="{{ old('name', $category->name) }}" placeholder="Masukkan nama kategori" required autofocus>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Nama kategori akan otomatis dibuatkan slug-nya</small>
            </div>

            <div class="col-md-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" 
                placeholder="Deskripsi kategori (opsional)">{{ old('description', $category->description) }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Berikan deskripsi yang jelas tentang kategori ini</small>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary me-auto">
              <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <button type="reset" class="btn btn-link">Reset</button>
            <button type="submit" class="btn btn-primary ms-2">
              <i class="ti ti-device-floppy"></i> Update
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection