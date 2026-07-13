@extends('layouts.app')

@section('title', 'Buat Pengembalian Material')

@section('page-pretitle', 'Pengembalian')
@section('page-title', 'Buat Pengembalian Material')

@section('content')
<div class="row">
  <div class="col-lg-8 mx-auto">
    <form action="{{ route('admin.returns.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Form Pengembalian Material</h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label required">Pilih Material</label>
              <select name="request_id" id="request-select" class="form-select {{ $eligibleMaterials->isEmpty() ? 'no-tomselect text-muted' : '' }} @error('request_id') is-invalid @enderror" required @disabled($eligibleMaterials->isEmpty())>
                <option value="">{{ $eligibleMaterials->isEmpty() ? 'Tidak ada material yang dapat dikembalikan saat ini' : 'Pilih material yang akan dikembalikan' }}</option>
                @foreach($eligibleMaterials as $item)
                  <option value="{{ $item->representative_request_id }}"
                          data-quantity="{{ $item->returnable_quantity }}"
                          data-unit="{{ $item->material->unit }}"
                          data-material="{{ $item->material->name }}"
                          {{ ($selectedRequestId == $item->representative_request_id) || old('request_id') == $item->representative_request_id ? 'selected' : '' }}>
                    {{ $item->material->name }} — {{ $item->returnable_quantity }} {{ $item->material->unit }} dapat dikembalikan @unless($isProduksi) ({{ $item->requester_name }}) @endunless
                  </option>
                @endforeach
              </select>
              @error('request_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint">Hanya material yang masih Anda pegang (belum dipakai/dikembalikan) yang dapat dikembalikan</small>
            </div>

            <div class="col-md-6">
              <label class="form-label required">Jumlah Pengembalian</label>
              <div class="input-group">
                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                       value="{{ old('quantity') }}" min="1" placeholder="0" required @disabled($eligibleMaterials->isEmpty())>
                <span class="input-group-text" id="unit-display">satuan</span>
              </div>
              @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-hint" id="quantity-info">Pilih permintaan terlebih dahulu</small>
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
            <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary me-auto">
              <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <button type="reset" class="btn btn-link">Reset</button>
            <button type="submit" class="btn btn-primary ms-2" @disabled($eligibleMaterials->isEmpty())>
              <i class="ti ti-send"></i> Kirim Pengembalian
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
    const requestSelect = document.getElementById('request-select');
    const unitDisplay = document.getElementById('unit-display');
    const quantityInfo = document.getElementById('quantity-info');
    const quantityInput = document.querySelector('input[name="quantity"]');
    
    function updateRequestInfo() {
      const option = requestSelect.options[requestSelect.selectedIndex];
      
      if (requestSelect.value) {
        const unit = option.dataset.unit || 'satuan';
        const quantity = parseInt(option.dataset.quantity || 0);
        const materialName = option.dataset.material || '';
        
        unitDisplay.textContent = unit;
        quantityInfo.textContent = `Sisa stok yang dapat dikembalikan tidak boleh melebihi: ${quantity} ${unit} (${materialName})`;
        quantityInput.setAttribute('max', quantity);
      } else {
        unitDisplay.textContent = 'satuan';
        quantityInfo.textContent = 'Pilih permintaan terlebih dahulu';
        quantityInput.removeAttribute('max');
      }
    }
    
    requestSelect.addEventListener('change', updateRequestInfo);
    updateRequestInfo();
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
      if (requestSelect.value) {
        const option = requestSelect.options[requestSelect.selectedIndex];
        const maxQuantity = parseInt(option.dataset.quantity || 0);
        const returnQuantity = parseInt(quantityInput.value);
        
        if (returnQuantity <= 0) {
          e.preventDefault();
          alert('Jumlah pengembalian harus lebih dari 0');
        } else if (returnQuantity > maxQuantity) {
          e.preventDefault();
          alert(`Jumlah pengembalian tidak boleh melebihi ${maxQuantity} ${option.dataset.unit}`);
        }
      }
    });
  });
</script>
@endpush