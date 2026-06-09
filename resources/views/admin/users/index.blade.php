@extends('layouts.app')

@section('title', 'Daftar Pengguna')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Pengguna')

@section('page-actions')
  @can('create-users')
  <div class="btn-list">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Tambah Pengguna
    </a>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Semua Pengguna</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'role']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.users.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-10">
            <label class="form-label">Pencarian</label>
            <div class="input-icon">
              <input type="text" class="form-control" name="search" placeholder="Cari nama atau email..." value="{{ request('search') }}">
              <span class="input-icon-addon">
                <i class="ti ti-search"></i>
              </span>
            </div>
          </div>

          <div class="col-md-2">
            <label class="form-label">Peran</label>
            <select name="role" class="form-select">
              <option value="">Semua Peran</option>
              @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
              <i class="ti ti-refresh me-2"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
      <thead>
        <tr>
          <th class="w-1">No.</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Peran</th>
          <th class="w-1">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $index => $user)
        <tr>
          <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
          <td class="text-nowrap">{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>
            @foreach($user->roles as $role)
              <span class="badge bg-blue-lt">{{ $role->name }}</span>
            @endforeach
          </td>
          <td class="text-nowrap">
            <div class="dropdown">
              <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                Aksi
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a href="{{ route('admin.users.show', $user) }}" class="dropdown-item">
                  <i class="ti ti-eye me-1 text-primary"></i> Detail
                </a>
                @can('edit-users')
                <a href="{{ route('admin.users.edit', $user) }}" class="dropdown-item">
                  <i class="ti ti-edit me-1 text-warning"></i> Edit
                </a>
                @endcan
                @can('delete-users')
                @if(Auth::id() != $user->id)
                <a href="#" class="dropdown-item text-danger" 
                   onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) document.getElementById('delete-form-{{ $user->id }}').submit();">
                  <i class="ti ti-trash me-1"></i> Hapus
                </a>
                <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                  @csrf
                  @method('DELETE')
                </form>
                @endif
                @endcan
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center py-4">
            <div class="empty">
              <div class="empty-icon">
                <i class="ti ti-users text-muted" style="font-size: 3rem"></i>
              </div>
              <p class="empty-title">Tidak ada data pengguna</p>
              <p class="empty-subtitle text-muted">
                Belum ada pengguna yang ditambahkan atau tidak ada pengguna yang sesuai dengan filter.
              </p>
              @can('create-users')
              <div class="empty-action">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                  <i class="ti ti-plus"></i> Tambah Pengguna Baru
                </a>
              </div>
              @endcan
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-secondary">
      Menampilkan <span>{{ $users->firstItem() ?? 0 }}</span> sampai <span>{{ $users->lastItem() ?? 0 }}</span> dari <span>{{ $users->total() }}</span> pengguna
    </p>
    <div class="ms-auto">
      {{ $users->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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

    // Keep filter open if back button is used and filters were active
    if (performance.getEntriesByType("navigation")[0].type === 'back_forward') {
      const hasFilters = @json(request()->hasAny(['search', 'role']));
      if (hasFilters) {
        const filterCollapse = document.getElementById('filter-collapse');
        if (filterCollapse) {
          new bootstrap.Collapse(filterCollapse, {
            show: true
          });
        }
      }
    }
  });
</script>
@endpush