@extends('layouts.app')

@section('title', 'Tambah Material')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Tambah Material')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.materials.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Material</h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label required">Nama Material</label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                value="{{ old('name') }}" placeholder="Masukkan nama material" required autofocus>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" 
                placeholder="Deskripsi material (opsional)">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                <option value="" selected disabled>Pilih satuan</option>
                <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>liter</option>
                <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>meter</option>
                <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box</option>
                <option value="roll" {{ old('unit') == 'roll' ? 'selected' : '' }}>roll</option>
              </select>
              @error('unit')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Batas Kritis</label>
              <div class="input-group">
                <input type="number" name="critical_threshold" class="form-control @error('critical_threshold') is-invalid @enderror"
                  value="{{ old('critical_threshold', 0) }}" min="0" step="0.001" onkeydown="return event.key !== '-'"required>
                <span class="input-group-text" id="unit-addon-threshold">{{ old('unit') ?: 'satuan' }}</span>
              </div>
              @error('critical_threshold')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Stok di bawah nilai ini akan dianggap kritis.</small>
            </div>
            
            <div class="col-md-6">
              <label class="form-label required">Stok Awal</label>
              <div class="input-group">
                <input type="number" name="initial_stock" class="form-control @error('initial_stock') is-invalid @enderror" 
                  value="{{ old('initial_stock', 0) }}" min="0" step="0.001"onkeydown="return event.key !== '-'"required>
                <span class="input-group-text" id="unit-addon-stock">{{ old('unit') ?: 'satuan' }}</span>
              </div>
              @error('initial_stock')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Jumlah stok awal yang tersedia</small>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary me-auto">
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.querySelector('select[name="unit"]');
    const unitAddonThreshold = document.getElementById('unit-addon-threshold');
    const unitAddonStock = document.getElementById('unit-addon-stock');

    function updateUnitLabels() {
      const unit = unitSelect.value || 'satuan';
      unitAddonThreshold.textContent = unit;
      unitAddonStock.textContent = unit;
    }

    unitSelect.addEventListener('change', updateUnitLabels);
    updateUnitLabels();
  });
</script>
@endpush