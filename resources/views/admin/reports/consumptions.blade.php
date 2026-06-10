@extends('layouts.app')

@section('title', 'Laporan Pemakaian')

@section('page-pretitle', 'Laporan')
@section('page-title', 'Laporan Pemakaian Material Produksi')

@section('page-actions')
  <div class="btn-list">
    <button type="button" class="btn btn-success d-none d-sm-inline-block" onclick="exportReport('xlsx')">
      <i class="ti ti-file-spreadsheet"></i> Export Excel
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
          <div class="subheader">Total Pemakaian</div>
        </div>
        <div class="h1 mb-3">{{ $summary['total_consumptions'] }}</div>
        <div class="text-muted">Catatan pemakaian material</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Total Jumlah</div>
        </div>
        <div class="h1 mb-3">{{ number_format($summary['total_quantity']) }}</div>
        <div class="text-muted">Unit material dipakai</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Material Berbeda</div>
        </div>
        <div class="h1 mb-3 text-info">{{ $summary['distinct_materials'] }}</div>
        <div class="text-muted">Jenis material dipakai</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Pemakai</div>
        </div>
        <div class="h1 mb-3 text-primary">{{ $summary['unique_consumers'] }}</div>
        <div class="text-muted">Pengguna produksi</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Data Pemakaian Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'consumer_id', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.reports.consumptions') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" class="form-control" name="search" placeholder="Cari material, pemakai, atau catatan..." value="{{ request('search') }}">
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
            <label class="form-label">Pemakai</label>
            <select name="consumer_id" class="form-select">
              <option value="">Semua Pemakai</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('consumer_id') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.reports.consumptions') }}" class="btn btn-outline-secondary">
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
            <th>Jumlah</th>
            <th>Satuan</th>
            <th>Pemakai</th>
            <th>Tanggal</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($consumptions as $index => $consumption)
            <tr>
              <td>{{ ($consumptions->currentPage() - 1) * $consumptions->perPage() + $index + 1 }}</td>
              <td>
                <strong>{{ $consumption->material->name }}</strong>
              </td>
              <td>
                @if($consumption->material->category)
                  <span class="badge bg-blue-lt">{{  $consumption->material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td>{{ (float) $consumption->quantity }}</td>
              <td>{{ $consumption->material->unit }}</td>
              <td>{{ $consumption->consumer->name ?? '-' }}</td>
              <td>{{ $consumption->created_at->format('d M Y H:i') }}</td>
              <td>{{ $consumption->notes ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-database text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data</p>
                  <p class="empty-subtitle text-muted">
                    Tidak ada data pemakaian yang sesuai dengan filter yang dipilih.
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
      Menampilkan <span>{{ $consumptions->firstItem() ?? 0 }}</span> sampai <span>{{ $consumptions->lastItem() ?? 0 }}</span> dari <span>{{ $consumptions->total() }}</span> data
    </p>
    <div class="ms-auto">
      {{ $consumptions->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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
  form.action = '{{ route("admin.reports.consumptions.export") }}';

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
