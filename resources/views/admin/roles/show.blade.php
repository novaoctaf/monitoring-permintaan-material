@extends('layouts.app')

@section('title', 'Detail Peran')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Detail Peran')

@section('page-actions')
  <div class="btn-list">
    @can('edit-roles')
    @if(!in_array($role->name, ['staff', 'store', 'produksi']))
    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning d-none d-sm-inline-block">
      <i class="ti ti-edit"></i> Edit
    </a>
    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning d-sm-none">
      <i class="ti ti-edit"></i>
    </a>
    @endif
    @endcan
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Informasi Peran</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Nama</div>
            <div class="datagrid-content fw-bold">{{ $role->name }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah Izin</div>
            <div class="datagrid-content">
              <span class="badge bg-blue-lt">{{ $role->permissions->count() }} izin</span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah Pengguna</div>
            <div class="datagrid-content">
              <span class="badge {{ $role->users->count() > 0 ? 'bg-green-lt' : 'bg-gray-lt' }}">
                {{ $role->users->count() }} pengguna
              </span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Dibuat</div>
            <div class="datagrid-content">{{ $role->created_at->format('d M Y, H:i') }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Terakhir Diupdate</div>
            <div class="datagrid-content">{{ $role->updated_at->format('d M Y, H:i') }}</div>
          </div>
        </div>
        
        @if(!in_array($role->name, ['staff', 'store', 'produksi']))
          @can('delete-roles')
          <div class="mt-4">
            <a href="#" class="btn btn-danger w-100" 
               onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus peran ini?')) document.getElementById('delete-form').submit();">
              <i class="ti ti-trash me-1"></i> Hapus Peran
            </a>
            <form id="delete-form" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-none">
              @csrf
              @method('DELETE')
            </form>
          </div>
          @endcan
        @endif
      </div>
    </div>
    
    @if($role->users->count() > 0)
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pengguna dengan Peran Ini</h3>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          @foreach($role->users->take(10) as $user)
            <div class="list-group-item">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="avatar" style="background-image: url({{ $user->avatar_url ?? '' }})">
                    {{ $user->initials ?? substr($user->name, 0, 2) }}
                  </span>
                </div>
                <div class="col text-truncate">
                  <a href="{{ route('admin.users.show', $user) }}" class="text-reset d-block">{{ $user->name }}</a>
                  <div class="d-block text-muted text-truncate mt-n1">{{ $user->email }}</div>
                </div>
                <div class="col-auto">
                  <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-light">
                    <i class="ti ti-eye"></i>
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @if($role->users->count() > 10)
        <div class="card-footer">
          <div class="text-center">
            <a href="{{ route('admin.users.index', ['role' => $role->id]) }}" class="btn btn-sm btn-primary">
              Lihat Semua Pengguna
            </a>
          </div>
        </div>
      @endif
    </div>
    @endif
  </div>
  
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
          <h3 class="card-title">Daftar Izin</h3>
          <span class="badge bg-blue-lt">{{ $role->permissions->count() }} izin</span>
        </div>
      </div>
      <div class="card-body">
        @if($role->permissions->count() > 0)
          <div class="space-y-4">
            @foreach($rolePermissions as $group => $groupPermissions)
              <div>
                <h4 class="mb-3">{{ ucfirst($group) }} 
                  <span class="badge bg-blue-lt">{{ $groupPermissions->count() }}</span>
                </h4>
                <div id="permission-{{ $group }}" class="accordion" role="tablist" aria-multiselectable="true">
                  <div class="accordion-item">
                    <button class="accordion-header" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $group }}" role="tab">
                      <h4 class="accordion-header-text">Daftar Izin {{ ucfirst($group) }}</h4>
                    </button>
                    <div id="collapse-{{ $group }}" class="accordion-collapse collapse" role="tabpanel" data-bs-parent="#permission-{{ $group }}">
                      <div class="accordion-body pt-0">
                        <div class="row g-3">
                          @foreach($groupPermissions as $permission)
                            <div class="col-md-6 col-lg-4">
                              <span class="badge bg-blue-lt">{{ $permission->name }}</span>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="empty">
            <div class="empty-icon">
              <i class="ti ti-lock-access-off text-muted" style="font-size: 3rem"></i>
            </div>
            <p class="empty-title">Tidak ada izin</p>
            <p class="empty-subtitle text-muted">
              Peran ini belum memiliki izin apa pun.
            </p>
            @can('edit-roles')
            <div class="empty-action">
              <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                <i class="ti ti-edit"></i> Edit Peran
              </a>
            </div>
            @endcan
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toast after 4 seconds
    setTimeout(function() {
      const toastElements = document.querySelectorAll('.toast.show');
      toastElements.forEach(function(toast) {
        const bsToast = new bootstrap.Toast(toast);
        bsToast.hide();
      });
    }, 4000);
  });
</script>
@endpush