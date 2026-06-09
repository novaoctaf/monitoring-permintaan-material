@extends('layouts.app')

@section('title', 'Daftar Kategori')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Kategori')

@section('page-actions')
  @can('create-inventory')
  <div class="btn-list">
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Tambah Kategori
    </a>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Daftar Kategori</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.categories.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Pencarian</label>
            <div class="input-icon">
              <input type="text" class="form-control" name="search" 
                     placeholder="Cari nama kategori..." value="{{ request('search') }}">
              <span class="input-icon-addon">
                <i class="ti ti-search"></i>
              </span>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
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
            <th>Nama Kategori</th>
            <th>Deskripsi</th>
            <th>Jumlah Material</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $index => $category)
            <tr>
              <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $index + 1 }}</td>
              <td class="text-nowrap">
                <strong>{{ $category->name }}</strong>
                <div class="text-muted">{{ $category->slug }}</div>
              </td>
              <td>{{ Str::limit($category->description, 50) ?: '-' }}</td>
              <td>
                <span class="badge bg-blue-lt">{{ $category->materials_count }} material</span>
              </td>
              <td class="text-nowrap">
                <div class="dropdown">
                  <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                    Aksi
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ route('admin.categories.show', $category) }}" class="dropdown-item">
                      <i class="ti ti-eye me-1 text-primary"></i> Detail
                    </a>
                    @can('edit-inventory')
                    <a href="{{ route('admin.categories.edit', $category) }}" class="dropdown-item">
                      <i class="ti ti-edit me-1 text-warning"></i> Edit
                    </a>
                    @endcan
                    @can('delete-inventory')
                    @if($category->materials_count == 0)
                    <a href="#" class="dropdown-item text-danger" 
                       onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus kategori ini?')) document.getElementById('delete-form-{{ $category->id }}').submit();">
                      <i class="ti ti-trash me-1"></i> Hapus
                    </a>
                    <form id="delete-form-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-none">
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
                    <i class="ti ti-category text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data kategori</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada kategori yang ditambahkan atau tidak ada kategori yang sesuai dengan filter.
                  </p>
                  @can('create-inventory')
                  <div class="empty-action">
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus"></i> Tambah Kategori Baru
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
      Menampilkan <span>{{ $categories->firstItem() ?? 0 }}</span> sampai <span>{{ $categories->lastItem() ?? 0 }}</span> dari <span>{{ $categories->total() }}</span> kategori
    </p>
    <div class="ms-auto">
      {{ $categories->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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
      const hasFilters = @json(request()->hasAny(['search']));
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