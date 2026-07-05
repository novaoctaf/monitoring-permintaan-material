@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Detail Pengguna')

@section('page-actions')
  <div class="btn-list">
    @can('edit-users')
    @if(Auth::id() != $user->id)
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning d-none d-sm-inline-block">
      <i class="ti ti-edit"></i> Edit
    </a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning d-sm-none">
      <i class="ti ti-edit"></i>
    </a>
    @endif
    @endcan
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
  </div>
@endsection

@section('content')
<div class="row row-cards">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body p-4 text-center">
        <span class="avatar avatar-xl mb-3 rounded-circle bg-primary-lt">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
        <h3 class="m-0">{{ $user->name }}</h3>
        <div class="text-muted">{{ $user->email }}</div>
        <div class="mt-3">
          @foreach($user->roles as $role)
            <span class="badge bg-blue-lt">{{ $role->name }}</span>
          @endforeach
        </div>
      </div>
      <div class="d-flex">
        @if(Auth::id() != $user->id)
        @can('delete-users')
        <a href="#" class="card-btn" onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) document.getElementById('delete-form').submit();">
          <i class="ti ti-trash text-red me-1"></i> Hapus
        </a>
        <form id="delete-form" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
          @csrf
          @method('DELETE')
        </form>
        @endcan
        @endif
      </div>
    </div>
  </div>
  
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Pengguna</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Nama</div>
            <div class="datagrid-content">{{ $user->name }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Email</div>
            <div class="datagrid-content">{{ $user->email }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Dibuat</div>
            <div class="datagrid-content">{{ $user->created_at->format('d F Y, H:i') }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Terakhir Diperbarui</div>
            <div class="datagrid-content">{{ $user->updated_at->format('d F Y, H:i') }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Peran</div>
            <div class="datagrid-content">
              @forelse($user->roles as $role)
                <span class="badge bg-blue-lt">{{ $role->name }}</span>
              @empty
                <span class="badge bg-gray-lt">Tidak ada peran</span>
              @endforelse
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Izin</div>
            <div class="datagrid-content">
              @forelse($user->getAllPermissions() as $permission)
                <span class="badge bg-green-lt">{{ $permission->name }}</span>
              @empty
                <span class="badge bg-gray-lt">Tidak ada izin khusus</span>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
    
    {{-- <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Aktivitas Terbaru</h3>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          Pencatatan aktivitas pengguna akan tersedia dalam pengembangan berikutnya.
        </div>
      </div> --}}
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