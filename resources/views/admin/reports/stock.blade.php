@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('page-pretitle', 'Laporan')
@section('page-title', 'Laporan Stok Material')

@section('page-actions')
  <div class="btn-list">
    <div class="btn-group d-none d-sm-inline-flex">
      <button type="button" class="btn btn-success" onclick="exportReport('xlsx')">
        <i class="ti ti-download me-1"></i> Export
      </button>
      <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="visually-hidden">Toggle Dropdown</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="javascript:void(0)" onclick="exportReport('xlsx')">
            <i class="ti ti-file-spreadsheet me-2"></i> Excel (.xlsx)
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0)" onclick="exportReport('csv')">
            <i class="ti ti-file-text me-2"></i> CSV (.csv)
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0)" onclick="exportReport('pdf')">
            <i class="ti ti-file-type-pdf me-2"></i> PDF (.pdf)
          </a>
        </li>
      </ul>
    </div>
    <button type="button" class="btn btn-success d-sm-none" onclick="exportReport('xlsx')">
      <i class="ti ti-download"></i>
    </button>
  </div>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="row mb-3">
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Total Material</div>
        </div>
        <div class="h1 mb-3">{{ $summary['total_materials'] }}</div>
        <div class="text-muted">Jenis material terdaftar</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Total Stok</div>
        </div>
        <div class="h1 mb-3">{{ number_format($summary['total_stock_value']) }}</div>
        <div class="text-muted">Unit tersedia</div>
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
        <div class="text-muted">Material ≤ 10 unit</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Stok Habis</div>
        </div>
        <div class="h1 mb-3 text-danger">{{ $summary['out_of_stock_count'] }}</div>
        <div class="text-muted">Material kosong</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Data Stok Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'stock_status']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.reports.stock') }}" method="GET">
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
              <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Habis (0)</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.reports.stock') }}" class="btn btn-outline-secondary">
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
            <th>Satuan</th>
            <th>Stok Saat Ini</th>
            <th>Status</th>
            <th>Terakhir Update</th>
          </tr>
        </thead>
        <tbody>
          @forelse($stocks as $index => $stock)
            <tr>
              <td>{{ ($stocks->currentPage() - 1) * $stocks->perPage() + $index + 1 }}</td>
              <td>
                <strong>{{ $stock->material->name }}</strong>
              </td>
              <td>
                @if($stock->material->category)
                  <span class="badge bg-blue-lt">{{ $stock->material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td>{{ $stock->material->unit }}</td>
              <td>
                <span class="badge {{  $stock->quantity <= 0 ? 'bg-danger' : ($stock->quantity <= 10 ? 'bg-warning' : 'bg-success') }}-lt">
                  {{ (float) $stock->quantity }} {{ $stock->material->unit }}
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
              <td>{{ $stock->updated_at->format('d M Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-database text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data</p>
                  <p class="empty-subtitle text-muted">
                    Tidak ada data stok yang sesuai dengan filter yang dipilih.
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
  
  // Create hidden iframe for download
  const iframe = document.createElement('iframe');
  iframe.style.display = 'none';
  document.body.appendChild(iframe);
  
  // Create form and submit to iframe
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route("admin.reports.stock.export") }}';
  form.target = iframe.name = 'download-iframe-' + Date.now();
  
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
  
  setTimeout(() => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Data berhasil diexport',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
    
    setTimeout(() => {
      document.body.removeChild(iframe);
      document.body.removeChild(form);
    }, 100);
  }, 500);
}
</script>
@endpush
