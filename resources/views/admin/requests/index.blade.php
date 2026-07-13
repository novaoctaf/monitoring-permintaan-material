@extends('layouts.app')

@section('title', 'Daftar Permintaan Material')

@section('page-pretitle', 'Permintaan')
@section('page-title', 'Daftar Permintaan Material')

@section('page-actions')
  @can('create-requests')
  <div class="btn-list">
    <a href="{{ route('admin.requests.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Buat Permintaan
    </a>
    <a href="{{ route('admin.requests.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Daftar Permintaan Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'status', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.requests.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Pencarian</label>
            <input type="text" class="form-control" name="search" placeholder="Cari nomor/material..." value="{{ request('search') }}">
          </div>
          
          <div class="col-md-2">
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
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">Semua Status</option>
              <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
              <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
              <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">Hingga Tanggal</label>
            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
          </div>

          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search me-2"></i> Cari
            </button>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary">
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
            <th>No. Permintaan</th>
            <th>Material</th>
            <th>Jumlah</th>
            <th>Peminta</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $req)
            <tr>
              <td>{{ $req->request_number }}</td>
              <td>
                <div class="d-flex flex-column">
                  <strong>{{ $req->material->name }}</strong>
                  @if($req->material->category)
                    <small class="text-muted">{{ $req->material->category->name }}</small>
                  @endif
                </div>
              </td>
              <td>{{ $req->quantity }} {{ $req->material->unit }}</td>
              <td>{{ $req->requester->name }}</td>
              <td>{{ $req->created_at->format('d M Y H:i') }}</td>
              <td>
                @if($req->status == 'pending')
                  <span class="badge bg-yellow-lt">Menunggu</span>
                @elseif($req->status == 'approved')
                  <span class="badge bg-green-lt">Disetujui</span>
                  @if($req->handover_status == 'received')
                    <span class="badge bg-teal-lt">Diterima</span>
                  @elseif($req->handover_status == 'handed_over')
                    <span class="badge bg-cyan-lt">Diserahkan</span>
                  @else
                    <span class="badge bg-azure-lt">Menunggu Penyerahan</span>
                  @endif
                @else
                  <span class="badge bg-red-lt">Ditolak</span>
                @endif
              </td>
              <td>
                <div class="btn-list flex-nowrap">
                  <a href="{{ route('admin.requests.show', $req) }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-eye"></i>
                  </a>
                  @role('store')
                    @if($req->status == 'approved' && !$req->handed_over_at)
                    <form action="{{ route('admin.requests.handover', $req) }}" method="POST"
                          onsubmit="return confirm('Serahkan barang untuk permintaan ini? Stok akan berkurang.');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-cyan">
                        <i class="ti ti-truck-delivery"></i> Serahkan
                      </button>
                    </form>
                    @endif
                  @endrole
                  @role('produksi')
                    @if($req->status == 'approved' && $req->handed_over_at && !$req->received_at && $req->requested_by == auth()->id())
                    <form action="{{ route('admin.requests.receive', $req) }}" method="POST"
                          onsubmit="return confirm('Konfirmasi barang sudah diterima? Stok produksi Anda akan bertambah.');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-teal">
                        <i class="ti ti-package-import"></i> Terima
                      </button>
                    </form>
                    @endif
                  @endrole
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-clipboard-text text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data permintaan</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada permintaan material yang dibuat atau tidak ada yang sesuai dengan filter yang dipilih.
                  </p>
                  @can('create-requests')
                  <div class="empty-action">
                    <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus"></i> Buat Permintaan Baru
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
      Menampilkan <span>{{ $requests->firstItem() ?? 0 }}</span> sampai <span>{{ $requests->lastItem() ?? 0 }}</span> dari <span>{{ $requests->total() }}</span> permintaan
    </p>
    <div class="ms-auto">
      {{ $requests->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>


@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
      const toastElements = document.querySelectorAll('.toast.show');
      toastElements.forEach(function(toast) {
        const bsToast = new bootstrap.Toast(toast);
        bsToast.hide();
      });
    }, 4000);

    if (performance.getEntriesByType("navigation")[0].type === 'back_forward') {
      const hasFilters = {{ json_encode(request()->hasAny(['search', 'category', 'status', 'date_from', 'date_to'])) }};
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