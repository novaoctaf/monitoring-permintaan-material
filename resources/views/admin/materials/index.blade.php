@extends('layouts.app')

@section('title', 'Daftar Material')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Material')

@section('page-actions')
  @can('create-inventory')
  <div class="btn-list">
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Tambah Material
    </a>
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Semua Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'unit', 'stock']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.materials.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Nama Material</label>
            <input type="text" class="form-control" name="search" placeholder="Cari nama material..." value="{{ request('search') }}">
          </div>
          
          <div class="col-md-4">
            <label class="form-label">Kategori</label>
            <select name="category" class="form-select">
              <option value="">Semua Kategori</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Satuan</label>
            <select name="unit" class="form-select">
              <option value="">Semua</option>
              <option value="pcs" {{ request('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
              <option value="kg" {{ request('unit') == 'kg' ? 'selected' : '' }}>kg</option>
              <option value="liter" {{ request('unit') == 'liter' ? 'selected' : '' }}>liter</option>
              <option value="meter" {{ request('unit') == 'meter' ? 'selected' : '' }}>meter</option>
              <option value="box" {{ request('unit') == 'box' ? 'selected' : '' }}>box</option>
              <option value="roll" {{ request('unit') == 'roll' ? 'selected' : '' }}>roll</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Stok</label>
            <select name="stock" class="form-select">
              <option value="">Semua</option>
              <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Stok Menipis (≤ 10)</option>
              <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Habis (0)</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
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
            <th>Nama Material</th>
            <th>Kategori</th>
            <th class="d-none d-md-table-cell">Deskripsi</th>
            <th>Satuan</th>
            @role('staff|store')
            <th>Stok</th>
            @endrole
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($materials as $index => $material)
            <tr>
              <td>{{ ($materials->currentPage() - 1) * $materials->perPage() + $index + 1 }}</td>
              <td class="text-nowrap">
                <strong>{{ $material->name }}</strong>
              </td>
              <td>
                @if($material->category)
                  <span class="badge bg-blue-lt">{{ $material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td class="d-none d-md-table-cell">{{ Str::limit($material->description, 50) ?: '-' }}</td>
              <td>{{ $material->unit }}</td>
              @role('staff|store')
              <td>
                @php
                  $stockQty = $material->stock->quantity ?? 0;
                  $badgeClass = $stockQty <= 0 ? 'bg-danger' : ($stockQty <= 10 ? 'bg-warning' : 'bg-success');
                @endphp
                <span class="badge {{ $badgeClass }}-lt">{{ (float) $stockQty }} {{ $material->unit }}</span>
              </td>
              @endrole
              <td class="text-nowrap">
                <div class="dropdown">
                  <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                    Aksi
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ route('admin.materials.show', $material) }}" class="dropdown-item">
                      <i class="ti ti-eye me-1 text-primary"></i> Detail
                    </a>
                    @can('edit-inventory')
                    <a href="{{ route('admin.materials.edit', $material) }}" class="dropdown-item">
                      <i class="ti ti-edit me-1 text-warning"></i> Edit
                    </a>
                    @endcan
                    @can('edit-stocks')
                    <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="dropdown-item">
                      <i class="ti ti-stack me-1 text-info"></i> Sesuaikan Stok
                    </a>
                    @endcan
                    @can('delete-inventory')
                    <a href="#" class="dropdown-item text-danger" 
                       onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus material ini?')) document.getElementById('delete-form-{{ $material->id }}').submit();">
                      <i class="ti ti-trash me-1"></i> Hapus
                    </a>
                    <form id="delete-form-{{ $material->id }}" action="{{ route('admin.materials.destroy', $material) }}" method="POST" class="d-none">
                      @csrf
                      @method('DELETE')
                    </form>
                    @endcan
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              @php
                $colspan = auth()->user()->hasRole(['staff', 'store']) ? 7 : 6;
              @endphp
              <td colspan="{{ $colspan }}" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-package text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data material</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada material yang ditambahkan atau tidak ada material yang sesuai dengan filter.
                  </p>
                  @can('create-inventory')
                  <div class="empty-action">
                    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus"></i> Tambah Material Baru
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
      Menampilkan <span>{{ $materials->firstItem() ?? 0 }}</span> sampai <span>{{ $materials->lastItem() ?? 0 }}</span> dari <span>{{ $materials->total() }}</span> data
    </p>
    <div class="ms-auto">
      {{ $materials->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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