@extends('layouts.app')

@section('title', 'Daftar Peran')

@section('page-pretitle', 'Manajemen Akses')
@section('page-title', 'Peran')

@section('page-actions')
  @can('create-roles')
  <div class="btn-list">
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Tambah Peran
    </a>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Semua Peran</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->has('search') ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.roles.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Pencarian</label>
            <div class="input-icon">
              <input type="text" class="form-control" name="search" placeholder="Cari nama peran..." value="{{ request('search') }}">
              <span class="input-icon-addon">
                <i class="ti ti-search"></i>
              </span>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
              <i class="ti ti-refresh me-2"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-striped table-hover">
        <thead class="sticky-top bg-light">
          <tr>
            <th class="w-1">No.</th>
            <th>Nama</th>
            <th>Izin</th>
            <th>Pengguna</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roles as $index => $role)
          <tr>
            <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $index + 1 }}</td>
            <td class="fw-bold">{{ $role->name }}</td>
            <td>
              @if($role->permissions_count > 0)
                <span class="badge bg-blue-lt">{{ $role->permissions_count }} izin</span>
              @else
                <span class="badge bg-gray-lt">Tidak ada izin</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $role->users->count() > 0 ? 'bg-green-lt' : 'bg-gray-lt' }}">
                {{ $role->users->count() }} pengguna
              </span>
            </td>
            <td class="text-nowrap">
              <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                  Aksi
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a href="{{ route('admin.roles.show', $role) }}" class="dropdown-item">
                    <i class="ti ti-eye me-1 text-primary"></i> Detail
                  </a>
                  @can('edit-roles')
                  <a href="{{ route('admin.roles.edit', $role) }}" class="dropdown-item">
                    <i class="ti ti-edit me-1 text-warning"></i> Edit
                  </a>
                  @endcan
                  @can('delete-roles')
                  @if(!in_array($role->name, ['staff', 'store', 'produksi']))
                  <a href="#" class="dropdown-item text-danger" 
                     onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus peran ini?')) document.getElementById('delete-form-{{ $role->id }}').submit();">
                    <i class="ti ti-trash me-1"></i> Hapus
                  </a>
                  <form id="delete-form-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-none">
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
                  <i class="ti ti-users-group text-muted" style="font-size: 3rem"></i>
                </div>
                <p class="empty-title">Tidak ada data peran</p>
                <p class="empty-subtitle text-muted">
                  Belum ada peran yang ditambahkan atau tidak ada peran yang sesuai dengan pencarian Anda.
                </p>
                @can('create-roles')
                <div class="empty-action">
                  <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Tambah Peran Baru
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
  </div>
  
  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-secondary">
      Menampilkan <span>{{ $roles->firstItem() ?? 0 }}</span> sampai <span>{{ $roles->lastItem() ?? 0 }}</span> dari <span>{{ $roles->total() }}</span> peran
    </p>
    <div class="ms-auto">
      {{ $roles->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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
      const hasFilters = @json(request()->has('search'));
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