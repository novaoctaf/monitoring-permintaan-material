@extends('layouts.app')

@section('title', 'Persetujuan Pengembalian Material')

@section('page-pretitle', 'Pengembalian')
@section('page-title', 'Persetujuan Pengembalian')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Pengembalian Menunggu Persetujuan</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.returns.approvals') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Pencarian</label>
            <input type="text" class="form-control" name="search" placeholder="Cari nomor/material..." value="{{ request('search') }}">
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

          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">
              <i class="ti ti-search"></i> Cari
            </button>
            <a href="{{ route('admin.returns.approvals') }}" class="btn btn-outline-secondary">
              <i class="ti ti-refresh"></i> Reset
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
            <th>Dikembalikan Oleh</th>
            <th>Tanggal</th>
            <th>No. Permintaan</th>
            <th class="w-1">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pendingReturns as $return)
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
                <a href="{{ route('admin.requests.show', $return->request) }}">{{ $return->request->request_number }}</a>
              </td>
              <td class="text-nowrap">
                <div class="btn-list">
                  <a href="{{ route('admin.returns.show', $return) }}" class="btn btn-sm btn-primary">
                    Detail
                  </a>
                  
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $return->id }}">
                      Setujui
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $return->id }}">
                      Tolak
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            
            <!-- Approve Modal -->
            <div class="modal modal-blur fade" id="approveModal{{ $return->id }}" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-body">
                    <div class="modal-title">Konfirmasi Persetujuan</div>
                    <div>Apakah Anda yakin ingin menyetujui pengembalian material ini?</div>
                    <div class="mt-2">
                      <div class="mb-1"><strong>Material:</strong> {{ $return->request->material->name }}</div>
                      <div class="mb-1"><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div>
                      <div class="mb-1"><strong>Dikembalikan oleh:</strong> {{ $return->returner->name }}</div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('admin.returns.approve', $return) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-success">Setujui</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Reject Modal -->
            <div class="modal modal-blur fade" id="rejectModal{{ $return->id }}" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                  <form action="{{ route('admin.returns.reject', $return) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                      <div class="modal-title">Konfirmasi Penolakan</div>
                      <div>Apakah Anda yakin ingin menolak pengembalian material ini?</div>
                      <div class="mt-2">
                        <div class="mb-3">
                          <label class="form-label required">Alasan Penolakan</label>
                          <input type="text" class="form-control" name="rejection_reason" placeholder="Masukkan alasan penolakan" required>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-clipboard-check text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada pengembalian menunggu persetujuan</p>
                  <p class="empty-subtitle text-muted">
                    Semua pengembalian material telah diproses atau belum ada pengembalian baru.
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
      Menampilkan <span>{{ $pendingReturns->firstItem() ?? 0 }}</span> sampai <span>{{ $pendingReturns->lastItem() ?? 0 }}</span> dari <span>{{ $pendingReturns->total() }}</span> pengembalian
    </p>
    <div class="ms-auto">
      {{ $pendingReturns->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>

@if(session('success') || session('error'))
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Notifikasi</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body {{ session('success') ? 'bg-success' : 'bg-danger' }} text-white">
      {{ session('success') ?? session('error') }}
    </div>
  </div>
</div>
@endif
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
      const hasFilters = @json(request()->hasAny(['search', 'category']));
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