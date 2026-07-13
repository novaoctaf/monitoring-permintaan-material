@extends('layouts.app')

@section('title', 'Buat Permintaan Material')

@section('page-pretitle', 'Permintaan')
@section('page-title', 'Buat Permintaan Material')

@section('content')
<div class="row">
  <div class="col-lg-8 mx-auto">
    <form action="{{ route('admin.requests.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Form Permintaan Material</h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label required">Material</label>
              <select name="material_id" id="material-select" class="form-select @error('material_id') is-invalid @enderror" required>
                <option value="">Pilih Material</option>
                @foreach($materials as $material)
                  @php
                    $stockQty = (float) $material->stock->quantity ?? 0;
                    $disabled = $stockQty <= 0;
                  @endphp
                  <option value="{{ $material->id }}" 
                          data-stock="{{ $stockQty }}" 
                          data-unit="{{ $material->unit }}" 
                          {{ old('material_id') == $material->id ? 'selected' : '' }}
                          {{ $disabled ? 'disabled' : '' }}>
                    {{ $material->name }} - {{ $material->category->name ?? 'No category' }} (Stok: {{ $stockQty }} {{ $material->unit }})
                  </option>
                @endforeach
              </select>
              @error('material_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              @role('produksi')
                <small class="form-hint">Pilih material yang dibutuhkan</small>
              @else
                <small class="form-hint">Material yang stoknya kosong tidak dapat dipilih</small>
              @endrole
            </div>

            <div class="col-md-6">
              <label class="form-label required">Jumlah</label>
              <div class="input-group">
                <input type="number" name="quantity"class="form-control @error('quantity') is-invalid @enderror"
                  value="{{ old('quantity', 1) }}" min="1" required>
                <span class="input-group-text" id="unit-display">satuan</span>
                @error('quantity')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              @role('staff|store')
                <small class="form-hint" id="stock-info">Pilih material terlebih dahulu</small>
              @endrole
            </div>

            <div class="col-md-12">
              <label class="form-label">Catatan</label>
              <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                        placeholder="Catatan atau keterangan tambahan (opsional)">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary me-auto">
              <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <button type="reset" class="btn btn-link">Reset</button>
            <button type="submit" class="btn btn-primary ms-2">
              <i class="ti ti-send"></i> Kirim Permintaan
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
    const materialSelect = document.getElementById('material-select');
    const unitDisplay = document.getElementById('unit-display');
    const stockInfo = document.getElementById('stock-info');
    const quantityInput = document.querySelector('input[name="quantity"]');
    
    // Check if user has role staff or store (can see stock info)
    const canSeeStock = {{ auth()->user()->hasAnyRole(['staff', 'store']) ? 'true' : 'false' }};
    
    function updateMaterialInfo() {
      const option = materialSelect.options[materialSelect.selectedIndex];
      const unit = option.dataset.unit || 'satuan';
      const stock = parseInt(option.dataset.stock || 0);
      
      unitDisplay.textContent = unit;
      
      if (canSeeStock && stockInfo) {
        if (materialSelect.value) {
          stockInfo.textContent = `Stok tersedia: ${stock} ${unit}`;
          quantityInput.setAttribute('max', stock);
        } else {
          stockInfo.textContent = 'Pilih material terlebih dahulu';
          quantityInput.removeAttribute('max');
        }
      }
    }
    
    materialSelect.addEventListener('change', updateMaterialInfo);
    updateMaterialInfo();
    
    // Form validation - only check stock for staff and store roles
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
      if (canSeeStock && materialSelect.value) {
        const option = materialSelect.options[materialSelect.selectedIndex];
        const stock = parseInt(option.dataset.stock || 0);
        const quantity = parseInt(quantityInput.value);
        
        if (quantity > stock) {
          e.preventDefault();
          alert(`Jumlah permintaan melebihi stok yang tersedia (${stock} ${option.dataset.unit})`);
        }
      }
    });
  });
</script>
@endpush