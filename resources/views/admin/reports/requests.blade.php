@extends('layouts.app')

@section('title', 'Laporan Permintaan Material')

@section('page-pretitle', 'Laporan')
@section('page-title', 'Laporan Permintaan Material')

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
          <div class="subheader">Total Permintaan</div>
        </div>
        <div class="h1 mb-3">{{ $summary['total_requests'] }}</div>
        <div class="text-muted">Semua permintaan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Disetujui</div>
        </div>
        <div class="h1 mb-3 text-success">{{ $summary['approved'] }}</div>
        <div class="text-muted">Permintaan approved</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Pending</div>
        </div>
        <div class="h1 mb-3 text-warning">{{ $summary['pending'] }}</div>
        <div class="text-muted">Menunggu persetujuan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">Ditolak</div>
        </div>
        <div class="h1 mb-3 text-danger">{{ $summary['rejected'] }}</div>
        <div class="text-muted">Permintaan rejected</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Data Permintaan Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'status', 'category', 'requester_id', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.reports.requests') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" class="form-control" name="search" placeholder="Nomor permintaan atau material..." value="{{ request('search') }}">
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">Semua</option>
              <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
              <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
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
            <label class="form-label">Pemohon</label>
            <select name="requester_id" class="form-select">
              <option value="">Semua Pemohon</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('requester_id') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.reports.requests') }}" class="btn btn-outline-secondary">
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
            <th>Nomor</th>
            <th>Tanggal</th>
            <th>Pemohon</th>
            <th>Material</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Disetujui</th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $index => $request)
            <tr>
              <td>{{ ($requests->currentPage() - 1) * $requests->perPage() + $index + 1 }}</td>
              <td>
                <a href="{{ route('admin.requests.show', $request) }}" class="text-reset">
                  <strong>{{ $request->request_number }}</strong>
                </a>
              </td>
              <td>{{ $request->created_at->format('d M Y') }}</td>
              <td>
                <div class="d-flex flex-column">
                  <span>{{ $request->requester->name }}</span>
                  <small class="text-muted">{{ $request->requester->getRoleNames()->first() }}</small>
                </div>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <strong>{{ $request->material->name }}</strong>
                  @if($request->material->category)
                    <small class="badge bg-blue-lt w-fit">{{ $request->material->category->name }}</small>
                  @endif
                </div>
              </td>
              <td>
                <span class="badge bg-azure-lt">
                  {{ $request->quantity }} {{ $request->material->unit }}
                </span>
              </td>
              <td>
                @if($request->status === 'approved')
                  <span class="badge bg-success-lt">Disetujui</span>
                @elseif($request->status === 'pending')
                  <span class="badge bg-warning-lt">Pending</span>
                @else
                  <span class="badge bg-danger-lt">Ditolak</span>
                @endif
              </td>
              <td>
                @if($request->approver)
                  <div class="d-flex flex-column">
                    <span>{{ $request->approver->name }}</span>
                    <small class="text-muted">{{ $request->approved_at->format('d M Y') }}</small>
                  </div>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
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
                    Tidak ada data permintaan yang sesuai dengan filter yang dipilih.
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
      Menampilkan <span>{{ $requests->firstItem() ?? 0 }}</span> sampai <span>{{ $requests->lastItem() ?? 0 }}</span> dari <span>{{ $requests->total() }}</span> data
    </p>
    <div class="ms-auto">
      {{ $requests->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
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
  form.action = '{{ route("admin.reports.requests.export") }}';
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
