@extends('layouts.app')

@section('title', 'Detail Izin')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Detail Izin')

@section('page-actions')
  <div class="btn-list">
    @can('edit-roles')
    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning d-none d-sm-inline-block">
      <i class="ti ti-edit"></i> Edit
    </a>
    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning d-sm-none">
      <i class="ti ti-edit"></i>
    </a>
    @endcan
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Informasi Izin</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Nama</div>
            <div class="datagrid-content">{{ $permission->name }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Grup</div>
            <div class="datagrid-content">
              @php
                $parts = explode('-', $permission->name);
                $group = count($parts) > 1 ? $parts[1] : 'other';
              @endphp
              <span class="badge bg-blue-lt">{{ ucfirst($group) }}</span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Aksi</div>
            <div class="datagrid-content">
              @php
                $parts = explode('-', $permission->name);
                $action = count($parts) > 0 ? $parts[0] : '';
              @endphp
              <span class="badge bg-purple-lt">{{ ucfirst($action) }}</span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah Peran</div>
            <div class="datagrid-content">
              <span class="badge {{ $rolesWithPermission->count() > 0 ? 'bg-green-lt' : 'bg-gray-lt' }}">{{ $rolesWithPermission->count() }} peran</span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Dibuat</div>
            <div class="datagrid-content">{{ $permission->created_at->format('d M Y, H:i') }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Terakhir Diperbarui</div>
            <div class="datagrid-content">{{ $permission->updated_at->format('d M Y, H:i') }}</div>
          </div>
        </div>
        
        @if($rolesWithPermission->count() == 0)
          @can('delete-roles')
          <div class="mt-4">
            <a href="#" class="btn btn-danger w-100" 
              onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus izin ini?')) document.getElementById('delete-form').submit();">
              <i class="ti ti-trash me-1"></i> Hapus Izin
            </a>
            <form id="delete-form" action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-none">
              @csrf
              @method('DELETE')
            </form>
          </div>
          @endcan
        @endif
      </div>
    </div>
  </div>
  
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Peran yang Menggunakan Izin Ini</h3>
      </div>
      <div class="card-body">
        @if($rolesWithPermission->count() > 0)
          <div class="table-responsive">
            <table class="table table-vcenter">
              <thead>
                <tr>
                  <th>Nama Peran</th>
                  <th>Jumlah Izin</th>
                  <th>Jumlah Pengguna</th>
                  <th class="w-1"></th>
                </tr>
              </thead>
              <tbody>
                @foreach($rolesWithPermission as $role)
                <tr>
                  <td class="fw-bold">{{ $role->name }}</td>
                  <td>
                    <span class="badge bg-blue-lt">{{ $role->permissions->count() }} izin</span>
                  </td>
                  <td>
                    <span class="badge {{ $role->users->count() > 0 ? 'bg-green-lt' : 'bg-gray-lt' }}">{{ $role->users->count() }} pengguna</span>
                  </td>
                  <td>
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-eye"></i> Detail
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="empty">
            <div class="empty-icon">
              <i class="ti ti-users-off text-muted" style="font-size: 3rem"></i>
            </div>
            <p class="empty-title">Tidak ada peran yang menggunakan izin ini</p>
            <p class="empty-subtitle text-muted">
              Izin ini belum ditetapkan ke peran apa pun dalam sistem.
            </p>
            @can('edit-roles')
            <div class="empty-action">
              <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Buat Peran Baru
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