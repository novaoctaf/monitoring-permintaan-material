@extends('layouts.app')

@section('title', 'Sesuaikan Stok')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Sesuaikan Stok')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Form Penyesuaian Stok</h3>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.stocks.adjust.submit') }}" method="POST">
          @csrf
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">Material</label>
              <select name="material_id" class="form-select @error('material_id') is-invalid @enderror" required>
                <option value="">Pilih Material</option>
                @foreach($materials as $material)
                  <option value="{{ $material->id }}" {{ old('material_id', request('material_id')) == $material->id ? 'selected' : '' }}>
                    {{ $material->name }} ({{ $material->stock->quantity ?? 0 }} {{ $material->unit }})
                  </option>
                @endforeach
              </select>
              @error('material_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Jumlah Penyesuaian</label>
              <div class="input-group">
                <input type="number" name="adjustment" class="form-control @error('adjustment') is-invalid @enderror" 
                       value="{{ old('adjustment', 0) }}" placeholder="Masukkan jumlah penyesuaian..." required>
                <span class="input-group-text" id="unit-addon"></span>
              </div>
              @error('adjustment')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Gunakan angka negatif untuk mengurangi stok</small>
            </div>

            <div class="col-md-12">
              <label class="form-label">Catatan</label>
              <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" 
                        placeholder="Tambahkan catatan penyesuaian (opsional)...">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline-secondary">
              <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-device-floppy"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialSelect = document.querySelector('select[name="material_id"]');
    const unitAddon = document.getElementById('unit-addon');
    
    function updateUnit() {
        const selectedOption = materialSelect.options[materialSelect.selectedIndex];
        const text = selectedOption.text;
        const unit = text.match(/\((.*?)\)/)?.[1].split(' ')[1] || 'satuan';
        unitAddon.textContent = unit;
    }
    
    materialSelect.addEventListener('change', updateUnit);
    updateUnit();
});
</script>
@endpush