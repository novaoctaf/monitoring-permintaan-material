@extends('layouts.app')

@section('title', 'Daftar Stok')

@section('page-pretitle', 'Inventory')
@section('page-title')
  @if(auth()->user()->hasRole('produksi') || request('view') === 'produksi')
    Stok Produksi
  @else
    Stok Gudang
  @endif
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">
        @if(auth()->user()->hasRole('produksi'))
          Stok Material Saya
        @elseif(request('view') === 'produksi')
          Stok Produksi
        @else
          Daftar Stok
        @endif
      </h3>
      <div class="card-actions">
        @role('produksi')
        <a href="{{ route('admin.stocks.my-history') }}" class="btn btn-sm btn-outline-primary me-2">
          <i class="ti ti-history me-1"></i> Riwayat Stok
        </a>
        @endrole
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'stock', 'view']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.stocks.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nama Material</label>
            <input type="text" class="form-control" name="search" placeholder="Cari nama material..." value="{{ request('search') }}">
          </div>
          
          <div class="col-md-3">
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

          @role('staff|store')
          <div class="col-md-3">
            <label class="form-label">Tampilan</label>
            <select name="view" class="form-select">
              <option value="warehouse" {{ request('view') != 'produksi' ? 'selected' : '' }}>Stok Gudang</option>
              <option value="produksi" {{ request('view') == 'produksi' ? 'selected' : '' }}>Stok Produksi</option>
            </select>
          </div>
          @endrole

          <div class="col-md-3">
            <label class="form-label">Status Stok</label>
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
            <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline-secondary">
              <i class="ti ti-refresh me-2"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-striped">
        <thead class="sticky-top bg-light">
          <tr>
            <th class="w-1">No.</th>
            <th>Material</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($stocks as $index => $stock)
            @php
              // Handle both Stock model (staff/store) and custom object (produksi)
              $material = $stock->material ?? $stock->material;
              $quantity = $stock->quantity ?? 0;
              $materialId = $stock->material_id ?? $stock->id;
            @endphp
            <tr>
              <td>{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $index + 1 }}</td>
              <td>
                <div class="d-flex flex-column">
                  <strong>{{ $material->name }}</strong>
                  <small class="text-muted">{{ $material->unit }}</small>
                </div>
              </td>
              <td>
                @if($material->category)
                  <span class="badge bg-blue-lt">{{ $material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td>
                @php
                  $badgeClass = $quantity <= 0 ? 'bg-danger' : ($quantity <= 10 ? 'bg-warning' : 'bg-success');
                @endphp
                <span class="badge {{ $badgeClass }}-lt">{{ (float) $quantity }} {{ $material->unit }}</span>
              </td>
              <td class="text-nowrap">
                <div class="dropdown">
                  <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">
                    Aksi
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    @role('staff|store')
                    <a href="{{ route('admin.stocks.show', $material) }}" class="dropdown-item">
                      <i class="ti ti-eye me-1 text-primary"></i> Detail
                    </a>
                    @can('edit-stocks')
                    <a href="{{ route('admin.stocks.adjust', ['material_id' => $materialId]) }}" class="dropdown-item">
                      <i class="ti ti-stack me-1 text-info"></i> Sesuaikan
                    </a>
                    @endcan
                    @else
                    <a href="{{ route('admin.materials.show', $material) }}" class="dropdown-item">
                      <i class="ti ti-eye me-1 text-primary"></i> Detail Material
                    </a>
                    @if($quantity > 0)
                    <a href="{{ route('admin.consumptions.create', ['material_id' => $materialId]) }}" class="dropdown-item">
                      <i class="ti ti-arrow-right me-1 text-warning"></i> Gunakan
                    </a>
                    <a href="{{ route('admin.returns.create', ['material_id' => $materialId]) }}" class="dropdown-item">
                      <i class="ti ti-arrow-back me-1 text-success"></i> Kembalikan
                    </a>
                    @endif
                    @endrole
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-stack text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data stok</p>
                  <p class="empty-subtitle text-muted">
                    @role('produksi')
                      Anda belum memiliki material. Silakan buat permintaan material terlebih dahulu.
                    @else
                      Tidak ada stok yang sesuai dengan filter yang dipilih.
                    @endrole
                  </p>
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
      Menampilkan <span>{{ $stocks->firstItem() ?? 0 }}</span> sampai <span>{{ $stocks->lastItem() ?? 0 }}</span> dari <span>{{ $stocks->total() }}</span> stok
    </p>
    <div class="ms-auto">
      {{ $stocks->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (performance.getEntriesByType("navigation")[0].type === 'back_forward') {
      const hasFilters = @json(request()->hasAny(['search', 'category', 'stock']));
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