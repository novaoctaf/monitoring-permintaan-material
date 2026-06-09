@extends('layouts.app')

@section('title', 'Detail Pengembalian Material')

@section('page-pretitle', 'Pengembalian')
@section('page-title', 'Detail Pengembalian')

@section('page-actions')
  <div class="btn-list">
    @if(auth()->user()->can('approve-returns') && $return->status == 'pending')
    <div class="btn-group">
      <button type="button" class="btn btn-success d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#approveModal">
        <i class="ti ti-check"></i> Setujui
      </button>
      <button type="button" class="btn btn-danger d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="ti ti-x"></i> Tolak
      </button>
    </div>
    @endif
    
    @if(auth()->user()->can('view-returns'))
    <a href="{{ auth()->user()->can('approve-returns') ? route('admin.returns.approvals') : route('admin.returns.index') }}" class="btn btn-outline-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
    @endif
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Pengembalian #{{ $return->return_number }}</h3>
        <div class="card-actions">
          @if($return->status == 'pending')
            <span class="badge bg-yellow-lt">Menunggu</span>
          @elseif($return->status == 'approved')
            <span class="badge bg-green-lt">Disetujui</span>
          @else
            <span class="badge bg-red-lt">Ditolak</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Material</div>
            <div class="datagrid-content fw-bold">{{ $return->request->material->name }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Kategori</div>
            <div class="datagrid-content">
              @if($return->request->material->category)
                <span class="badge bg-blue-lt">{{ $return->request->material->category->name }}</span>
              @else
                <span class="badge bg-gray-lt">Tanpa Kategori</span>
              @endif
            </div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah Pengembalian</div>
            <div class="datagrid-content">{{ $return->quantity }} {{ $return->request->material->unit }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah Permintaan Asli</div>
            <div class="datagrid-content">{{ $return->request->quantity }} {{ $return->request->material->unit }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">No. Permintaan</div>
            <div class="datagrid-content">
              <a href="{{ route('admin.requests.show', $return->request) }}">
                {{ $return->request->request_number }}
              </a>
            </div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Dikembalikan Oleh</div>
            <div class="datagrid-content">{{ $return->returner->name }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Pengembalian</div>
            <div class="datagrid-content">{{ $return->created_at->format('d M Y H:i') }}</div>
          </div>
          
          @if($return->status != 'pending')
          <div class="datagrid-item">
            <div class="datagrid-title">Diproses Oleh</div>
            <div class="datagrid-content">{{ $return->approver->name ?? '-' }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Diproses</div>
            <div class="datagrid-content">{{ $return->approved_at ? $return->approved_at->format('d M Y H:i') : '-' }}</div>
          </div>
          @endif
          
          @if($return->notes)
          <div class="datagrid-item col-span-2">
            <div class="datagrid-title">Catatan</div>
            <div class="datagrid-content">{{ $return->notes }}</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Stok</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Stok Saat Ini</div>
            <div class="datagrid-content">
              @php
                $stockQty = $return->request->material->stock->quantity ?? 0;
                $badgeClass = $stockQty <= 0 ? 'bg-red' : ($stockQty <= 10 ? 'bg-yellow' : 'bg-green');
              @endphp
              <span class="badge {{ $badgeClass }}-lt">{{(float) $stockQty }} {{ $return->request->material->unit }}</span>
            </div>
          </div>

          @if($return->status == 'pending')
          <div class="datagrid-item">
            <div class="datagrid-title">Stok Setelah Disetujui</div>
            <div class="datagrid-content">
              <span class="badge bg-blue-lt">{{ $stockQty + $return->quantity }} {{ $return->request->material->unit }}</span>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@if(auth()->user()->can('approve-returns') && $return->status == 'pending')
<!-- Approve Modal -->
<div class="modal modal-blur fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
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
<div class="modal modal-blur fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
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
@endif

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
  });
</script>
@endpush