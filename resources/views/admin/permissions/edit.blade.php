@extends('layouts.app')

@section('title', 'Edit Izin')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Edit Izin')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
      @csrf
      @method('PUT')
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Form Edit Izin</h3>
        </div>
        <div class="card-body">
          <div class="alert alert-info">
            <div class="d-flex">
              <div>
                <i class="ti ti-info-circle icon alert-icon"></i>
              </div>
              <div>
                <h4>Format Nama Izin</h4>
                <p class="text-muted">Format yang direkomendasikan adalah <code>action-resource</code>, misalnya: <code>create-users</code>, <code>edit-roles</code>, <code>view-inventory</code>.</p>
                <p class="text-muted mb-0">Konsistensi dalam penamaan akan memudahkan pengelolaan izin.</p>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label required">Nama Izin</label>
            <div class="input-group mb-2">
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $permission->name) }}" required autofocus>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          @if(count($permissionGroups) > 0)
          <div class="mb-3">
            <label class="form-label d-block">Grup Izin yang Tersedia</label>
            <div class="d-flex flex-wrap gap-2 mt-2">
              @foreach($permissionGroups as $group)
                <span class="badge bg-blue-lt cursor-pointer permission-group-badge">{{ $group }}</span>
              @endforeach
            </div>
            <small class="text-muted">Klik pada grup untuk menggunakan format yang sama</small>
          </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Contoh Format</label>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="view" data-resource="">view-</span>
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="create" data-resource="">create-</span>
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="edit" data-resource="">edit-</span>
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="delete" data-resource="">delete-</span>
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="approve" data-resource="">approve-</span>
              <span class="badge bg-purple-lt cursor-pointer permission-format-badge" data-action="reject" data-resource="">reject-</span>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <div class="d-flex">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary me-auto">
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

@if($permission->roles->count() > 0)
<div class="row mt-3">
  <div class="col-xl-12 mx-auto">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Peran yang Menggunakan Izin Ini</h3>
      </div>
      <div class="card-body">
        <div class="tags">
          @foreach($permission->roles as $role)
            <a href="{{ route('admin.roles.show', $role) }}" class="badge bg-blue-lt me-1 mb-1 text-decoration-none">
              {{ $role->name }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Make permission group badges clickable
    const permissionGroupBadges = document.querySelectorAll('.permission-group-badge');
    const permissionFormatBadges = document.querySelectorAll('.permission-format-badge');
    const nameInput = document.querySelector('input[name="name"]');

    permissionGroupBadges.forEach(badge => {
      badge.addEventListener('click', function() {
        const group = this.textContent.trim();
        const currentValue = nameInput.value;
        
        // If there's already a resource part in the current value, replace it
        if (currentValue.includes('-')) {
          const action = currentValue.split('-')[0];
          nameInput.value = `${action}-${group}`;
        } else {
          // Otherwise just append it with a dash
          nameInput.value = currentValue ? `${currentValue}-${group}` : `view-${group}`;
        }
      });
    });

    permissionFormatBadges.forEach(badge => {
      badge.addEventListener('click', function() {
        const action = this.dataset.action;
        const currentValue = nameInput.value;
        
        // If there's already a resource part in the current value, keep it
        if (currentValue.includes('-')) {
          const resource = currentValue.split('-')[1];
          nameInput.value = `${action}-${resource}`;
        } else {
          // Otherwise just set the action part
          nameInput.value = action + '-';
        }
      });
    });
  });
</script>
@endpush