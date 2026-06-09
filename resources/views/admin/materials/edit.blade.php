@extends('layouts.app')

@section('title', 'Edit Material')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Edit Material')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.materials.update', $material) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Material</h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label required">Nama Material</label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                value="{{ old('name', $material->name) }}" placeholder="Masukkan nama material" required autofocus>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" 
                placeholder="Deskripsi material (opsional)">{{ old('description', $material->description) }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id', $material->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
              @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Pilih kategori untuk memudahkan pengelompokan material</small>
            </div>
            
            <div class="col-md-6">
              <label class="form-label required">Satuan</label>
              <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                <option value="" disabled>Pilih satuan</option>
                <option value="pcs" {{ old('unit', $material->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                <option value="kg" {{ old('unit', $material->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                <option value="liter" {{ old('unit', $material->unit) == 'liter' ? 'selected' : '' }}>liter</option>
                <option value="meter" {{ old('unit', $material->unit) == 'meter' ? 'selected' : '' }}>meter</option>
                <option value="box" {{ old('unit', $material->unit) == 'box' ? 'selected' : '' }}>box</option>
                <option value="roll" {{ old('unit', $material->unit) == 'roll' ? 'selected' : '' }}>roll</option>
              </select>
              @error('unit')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Batas Kritis</label>
              <div class="input-group">
                <input type="number" name="critical_threshold" class="form-control @error('critical_threshold') is-invalid @enderror"
                  value="{{ old('critical_threshold', $material->critical_threshold ?? 0) }}" min="0" step="0.001" required>
                <span class="input-group-text">{{ $material->unit }}</span>
              </div>
              @error('critical_threshold')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Stok di bawah nilai ini akan dianggap kritis.</small>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Stok Saat Ini</label>
              <div class="input-group">
                <input type="text" class="form-control" value="{{ $material->stock->quantity ?? 0 }}" disabled readonly>
                <span class="input-group-text">{{ $material->unit }}</span>
              </div>
              <small class="form-hint">Untuk mengubah stok, gunakan fitur <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}">Sesuaikan Stok</a></small>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.materials.show', $material) }}" class="btn btn-outline-secondary me-auto">
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