@extends('layouts.app')

@section('title', 'Laporan Stok Produksi')

@section('page-pretitle', 'Laporan')
@section('page-title', 'Laporan Stok Material Produksi')

@section('page-actions')
  <div class="btn-list">
    <button type="button" class="btn btn-success d-none d-sm-inline-block" onclick="exportReport('xlsx')">
      <i class="ti ti-file-spreadsheet"></i> Export Excel
    </button>
    <button type="button" class="btn btn-outline-success d-none d-sm-inline-block" onclick="exportReport('csv')">
      <i class="ti ti-file-text"></i> Export CSV
    </button>
    <button type="button" class="btn btn-success d-sm-none" onclick="exportReport('xlsx')">
      <i class="ti ti-file-spreadsheet"></i>
    </button>
  </div>
@endsection

@section('content')
<div class="row mb-3">
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Total Material</div>
        </div>
        <div class="h1 mb-3">{{ $summary['total_materials'] }}</div>
        <div class="text-muted">Material terdaftar</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Stok Produksi</div>
        </div>
        <div class="h1 mb-3">{{ number_format($summary['total_production_stock']) }}</div>
        <div class="text-muted">Total stok material produksi</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Stok Menipis</div>
        </div>
        <div class="h1 mb-3 text-warning">{{ $summary['low_stock_count'] }}</div>
        <div class="text-muted">Material produksi ≤ 10</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Material Tersedia</div>
        </div>
        <div class="h1 mb-3 text-success">{{ $summary['available_count'] }}</div>
        <div class="text-muted">Material produksi tersedia</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Data Stok Produksi</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'stock_status']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.reports.production.stock') }}" method="GET">
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
          <div class="col-md-4">
            <label class="form-label">Status Stok</label>
            <select name="stock_status" class="form-select">
              <option value="">Semua</option>
              <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis (≤ 10)</option>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.reports.production.stock') }}" class="btn btn-outline-secondary">
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
            <th>Jumlah Stok Produksi</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($stocks as $index => $stock)
            <tr>
              <td>{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $index + 1 }}</td>
              <td><strong>{{ $stock->material->name }}</strong></td>
              <td>
                @if($stock->material->category)
                  <span class="badge bg-blue-lt">{{ $stock->material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td>
                <span class="badge {{ $stock->quantity <= 0 ? 'bg-danger' : ($stock->quantity <= 10 ? 'bg-warning' : 'bg-success') }}-lt">
                  {{ $stock->quantity }} {{ $stock->material->unit }}
                </span>
              </td>
              <td>
                @if($stock->quantity <= 0)
                  <span class="badge bg-danger-lt">Habis</span>
                @elseif($stock->quantity <= 10)
                  <span class="badge bg-warning-lt">Menipis</span>
                @else
                  <span class="badge bg-success-lt">Tersedia</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-database text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data</p>
                  <p class="empty-subtitle text-muted">Tidak ada data stok produksi yang sesuai dengan filter.</p>
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
      Menampilkan <span>{{ $stocks->firstItem() ?? 0 }}</span> sampai <span>{{ $stocks->lastItem() ?? 0 }}</span> dari <span>{{ $stocks->total() }}</span> data
    </p>
    <div class="ms-auto">
      {{ $stocks->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function exportReport(format) {
  const params = new URLSearchParams(window.location.search);
  params.append('format', format);

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route("admin.reports.production.stock.export") }}';

  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = '{{ csrf_token() }}';
  form.appendChild(csrfInput);

  params.forEach((value, key) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = value;
    form.appendChild(input);
  });

  document.body.appendChild(form);
  form.submit();
}
</script>
@endpush
