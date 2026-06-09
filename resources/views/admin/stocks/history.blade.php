@extends('layouts.app')

@section('title', 'Riwayat Stok')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Riwayat Stok')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Riwayat Penyesuaian Stok</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'type']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.stocks.history') }}" method="GET">
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

          <div class="col-md-3">
            <label class="form-label">Tipe Perubahan</label>
            <select name="type" class="form-select">
              <option value="">Semua</option>
              <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
              <option value="request" {{ request('type') == 'request' ? 'selected' : '' }}>Permintaan</option>
              <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Pengembalian</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.stocks.history') }}" class="btn btn-outline-secondary">
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
          <th>Tanggal</th>
          <th>Material</th>
          <th>Kategori</th>
          <th>Sebelum</th>
          <th>Perubahan</th>
          <th>Sesudah</th>
          <th>Tipe</th>
          <th>Oleh</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($adjustments as $adjustment)
          <tr>
            <td class="text-nowrap">{{ $adjustment->created_at->format('d M Y H:i') }}</td>
            <td>
              <div class="d-flex flex-column">
                <strong>{{ $adjustment->material->name }}</strong>
                <small class="text-muted">{{ $adjustment->material->unit }}</small>
              </div>
            </td>
            <td>
              @if($adjustment->material->category)
                <span class="badge bg-blue-lt">{{ $adjustment->material->category->name }}</span>
              @else
                <span class="badge bg-gray-lt">Tanpa Kategori</span>
              @endif
            </td>
            <td>{{ $adjustment->quantity_before }}</td>
            <td>
              @if($adjustment->adjustment_quantity > 0)
                <span class="text-success">+{{ $adjustment->adjustment_quantity }}</span>
              @else
                <span class="text-danger">{{ $adjustment->adjustment_quantity }}</span>
              @endif
            </td>
            <td>{{ $adjustment->quantity_after }}</td>
            <td>
              @if($adjustment->type === 'manual')
                <span class="badge bg-blue-lt">Manual</span>
              @elseif($adjustment->type === 'request')
                <span class="badge bg-green-lt">Permintaan</span>
              @else
                <span class="badge bg-purple-lt">Pengembalian</span>
              @endif
            </td>
            <td>{{ $adjustment->user->name }}</td>
            <td>{{ $adjustment->notes ?: '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-4">
              <div class="empty">
                <div class="empty-icon">
                  <i class="ti ti-history text-muted" style="font-size: 3rem"></i>
                </div>
                <p class="empty-title">Tidak ada riwayat</p>
                <p class="empty-subtitle text-muted">
                  Belum ada riwayat penyesuaian stok yang tercatat.
                </p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-secondary">
      Menampilkan <span>{{ $adjustments->firstItem() ?? 0 }}</span> sampai <span>{{ $adjustments->lastItem() ?? 0 }}</span> dari <span>{{ $adjustments->total() }}</span> riwayat
    </p>
    <div class="ms-auto">
      {{ $adjustments->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (performance.getEntriesByType("navigation")[0].type === 'back_forward') {
      const hasFilters = @json(request()->hasAny(['search', 'category', 'type']));
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