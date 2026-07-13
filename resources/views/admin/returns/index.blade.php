@extends('layouts.app')

@section('title', 'Daftar Pengembalian Material')

@section('page-pretitle', 'Pengembalian')
@section('page-title', 'Daftar Pengembalian Material')

@section('page-actions')
  @can('create-returns')
  <div class="btn-list">
    <a href="{{ route('admin.returns.create') }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-plus"></i> Buat Pengembalian
    </a>
    <a href="{{ route('admin.returns.create') }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-plus"></i>
    </a>
  </div>
  @endcan
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Daftar Pengembalian Material</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'status', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.returns.index') }}" method="GET">
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
            <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">
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
            <th>No. Pengembalian</th>
            <th>Material</th>
            <th>Jumlah</th>
            <th>Pengembalian Oleh</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($returns as $return)
            <tr>
              <td>{{ $return->return_number }}</td>
              <td>
                <div class="d-flex flex-column">
                  <strong>{{ $return->request->material->name }}</strong>
                  @if($return->request->material->category)
                    <small class="text-muted">{{ $return->request->material->category->name }}</small>
                  @endif
                </div>
              </td>
              <td>{{ $return->quantity }} {{ $return->request->material->unit }}</td>
              <td>{{ $return->returner->name }}</td>
              <td>{{ $return->created_at->format('d M Y H:i') }}</td>
              <td>
                @if($return->status == 'pending')
                  <span class="badge bg-yellow-lt">Menunggu</span>
                @elseif($return->status == 'approved')
                  <span class="badge bg-green-lt">Disetujui</span>
                  @if($return->handover_status == 'received')
                    <span class="badge bg-teal-lt">Diterima</span>
                  @elseif($return->handover_status == 'handed_over')
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
                  <a href="{{ route('admin.returns.show', $return) }}" class="btn btn-sm btn-icon btn-primary" title="Lihat detail">
                    <i class="ti ti-eye"></i>
                  </a>

                  @if(auth()->user()->can('approve-returns') && !auth()->user()->hasRole('store') && $return->status == 'pending')
                    <button type="button" class="btn btn-sm btn-success js-confirm-action"
                            data-action="{{ route('admin.returns.approve', $return) }}"
                            data-title="Setujui Pengembalian {{ $return->return_number }}"
                            data-note="Setelah disetujui, barang diserahkan oleh produksi."
                            data-body="<div class='mb-1'><strong>Material:</strong> {{ $return->request->material->name }}</div><div class='mb-1'><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div><div class='mb-1'><strong>Dikembalikan oleh:</strong> {{ $return->returner->name }}</div>"
                            data-confirm-label="Setujui" data-confirm-class="btn-success">
                      <i class="ti ti-check"></i> Setujui
                    </button>
                    <button type="button" class="btn btn-sm btn-danger js-reject-action"
                            data-action="{{ route('admin.returns.reject', $return) }}"
                            data-title="Tolak Pengembalian {{ $return->return_number }}">
                      <i class="ti ti-x"></i> Tolak
                    </button>
                  @endif

                  @role('produksi')
                    @if($return->status == 'approved' && !$return->handed_over_at && $return->returned_by == auth()->id())
                    <button type="button" class="btn btn-sm btn-cyan js-confirm-action"
                            data-action="{{ route('admin.returns.handover', $return) }}"
                            data-title="Serahkan Barang · {{ $return->return_number }}"
                            data-note="Barang dikembalikan ke store."
                            data-body="<div class='mb-1'><strong>Material:</strong> {{ $return->request->material->name }}</div><div class='mb-1'><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div>"
                            data-confirm-label="Serahkan" data-confirm-class="btn-cyan">
                      <i class="ti ti-truck-delivery"></i> Serahkan
                    </button>
                    @endif
                  @endrole

                  @role('store')
                    @if($return->status == 'approved' && $return->handed_over_at && !$return->received_at)
                    <button type="button" class="btn btn-sm btn-teal js-confirm-action"
                            data-action="{{ route('admin.returns.receive', $return) }}"
                            data-title="Terima Barang · {{ $return->return_number }}"
                            data-note="Stok utama akan bertambah."
                            data-body="<div class='mb-1'><strong>Material:</strong> {{ $return->request->material->name }}</div><div class='mb-1'><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div><div class='mb-1'><strong>Dikembalikan oleh:</strong> {{ $return->returner->name }}</div>"
                            data-confirm-label="Terima" data-confirm-class="btn-teal">
                      <i class="ti ti-package-import"></i> Terima
                    </button>
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
                    <i class="ti ti-arrow-back-up text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada data pengembalian</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada pengembalian material yang dibuat atau tidak ada yang sesuai dengan filter yang dipilih.
                  </p>
                  @can('create-returns')
                  <div class="empty-action">
                    <a href="{{ route('admin.returns.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus"></i> Buat Pengembalian Baru
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
      Menampilkan <span>{{ $returns->firstItem() ?? 0 }}</span> sampai <span>{{ $returns->lastItem() ?? 0 }}</span> dari <span>{{ $returns->total() }}</span> pengembalian
    </p>
    <div class="ms-auto">
      {{ $returns->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>

@include('layouts.partials.action-modals')

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