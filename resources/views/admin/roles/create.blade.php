@extends('layouts.app')

@section('title', 'Tambah Peran')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Tambah Peran')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.roles.store') }}" method="POST">
      @csrf
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Peran</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label required">Nama Peran</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                <small class="form-hint">Nama peran sebaiknya singkat dan deskriptif</small>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mt-3">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="card-title">Izin</h3>
            <div class="card-actions btn-group">
              <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                Pilih Semua
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                Batalkan Semua
              </button>
            </div>
          </div>
        </div>
        <div class="card-body border-bottom">
          <div class="alert alert-info">
            <div class="d-flex">
              <div>
                <i class="ti ti-info-circle icon alert-icon"></i>
              </div>
              <div>
                Pilih izin yang akan diberikan kepada peran ini.
              </div>
            </div>
          </div>
          
          @foreach($permissions as $resource => $resourcePermissions)
            <div class="mb-3">
              <h4 class="mb-3">{{ ucfirst($resource) }} 
                <span class="badge bg-blue-lt">{{ $resourcePermissions->count() }}</span>
              </h4>
              <div id="permission-{{ $resource }}" class="accordion" role="tablist" aria-multiselectable="true">
                <div class="accordion-item">
                  <button class="accordion-header" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $resource }}" role="tab">
                    <h4 class="accordion-header-text">Daftar Izin {{ ucfirst($resource) }}</h4>
                  </button>
                  <div id="collapse-{{ $resource }}" class="accordion-collapse collapse" role="tabpanel" data-bs-parent="#permission-{{ $resource }}">
                    <div class="accordion-body pt-0">
                      <div class="row g-3">
                        @foreach($resourcePermissions as $permission)
                          <div class="col-md-6 col-lg-4">
                            <label class="form-check form-switch">
                              <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                    class="form-check-input permission-checkbox" 
                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                              <span class="form-check-label">{{ $permission->name }}</span>
                            </label>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach

          @error('permissions')
            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
          @enderror
        </div>
        <div class="card-footer">
          <div class="d-flex">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary me-auto">
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
    const selectAllBtn = document.getElementById('select-all');
    const deselectAllBtn = document.getElementById('deselect-all');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    
    selectAllBtn.addEventListener('click', function() {
      permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
      });
    });
    
    deselectAllBtn.addEventListener('click', function() {
      permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
      });
    });
  });
</script>
@endpush