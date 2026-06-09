@extends('layouts.app')

@section('title', 'Detail Permintaan Material')

@section('page-pretitle', 'Permintaan')
@section('page-title', 'Detail Permintaan')

@section('page-actions')
  <div class="btn-list">
    @if(auth()->user()->can('approve-requests') && $request->status == 'pending')
    <div class="btn-group">
      @if($request->material->stock && $request->material->stock->quantity >= $request->quantity)
      <button type="button" class="btn btn-success d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#approveModal">
        <i class="ti ti-check"></i> Setujui
      </button>
      @else
      <button type="button" class="btn btn-success d-none d-sm-inline-block" disabled title="Stok tidak cukup">
        <i class="ti ti-check"></i> Setujui
      </button>
      @endif
      <button type="button" class="btn btn-danger d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="ti ti-x"></i> Tolak
      </button>
    </div>
    @endif
    
    @if(auth()->user()->can('view-requests'))
    <a href="{{ auth()->user()->can('approve-requests') ? route('admin.requests.approvals') : route('admin.requests.index') }}" class="btn btn-outline-secondary">
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
        <h3 class="card-title">Informasi Permintaan #{{ $request->request_number }}</h3>
        <div class="card-actions">
          @if($request->status == 'pending')
            <span class="badge bg-yellow-lt">Menunggu</span>
          @elseif($request->status == 'approved')
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
            <div class="datagrid-content fw-bold">{{ $request->material->name }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Kategori</div>
            <div class="datagrid-content">
              @if($request->material->category)
                <span class="badge bg-blue-lt">{{ $request->material->category->name }}</span>
              @else
                <span class="badge bg-gray-lt">Tanpa Kategori</span>
              @endif
            </div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Jumlah</div>
            <div class="datagrid-content">{{ $request->quantity }} {{ $request->material->unit }}</div>
          </div>
          
          {{-- <div class="datagrid-item">
            <div class="datagrid-title">Stok Tersedia</div>
            <div class="datagrid-content">
              @php
                $stockQty = $request->material->stock->quantity ?? 0;
                $sufficient = $stockQty >= $request->quantity;
                $badgeClass = $sufficient ? 'bg-success' : 'bg-danger';
              @endphp
              <span class="badge {{ $badgeClass }}-lt">{{(float) $stockQty }} {{ $request->material->unit }}</span>
            </div>
          </div> --}}
          
          <div class="datagrid-item">
            <div class="datagrid-title">Peminta</div>
            <div class="datagrid-content">{{ $request->requester->name }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Permintaan</div>
            <div class="datagrid-content">{{ $request->created_at->format('d M Y H:i') }}</div>
          </div>
          
          @if($request->status != 'pending')
          <div class="datagrid-item">
            <div class="datagrid-title">Diproses Oleh</div>
            <div class="datagrid-content">{{ $request->approver->name ?? '-' }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Diproses</div>
            <div class="datagrid-content">{{ $request->approved_at ? $request->approved_at->format('d M Y H:i') : '-' }}</div>
          </div>
          @endif
          
          @if($request->notes)
          <div class="datagrid-item col-span-2">
            <div class="datagrid-title">Catatan</div>
            <div class="datagrid-content">{{ $request->notes }}</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  
  
  <div class="col-lg-4">
    <!-- Return Information if any -->
    @if($request->status == 'approved' && $request->returns->count() > 0)
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Informasi Pengembalian</h3>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr>
              <th>Id</th> {{-- No. Pengembalian to Id for clarity --}}
              <th>Tanggal</th>
              <th>Jumlah</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($request->returns as $return)
            <tr>
              <td>{{ $return->return_number }}</td>
              <td>{{ $return->created_at->format('d M Y') }}</td>
              <td>{{ $return->quantity }} {{ $request->material->unit }}</td>
              <td>
                @if($return->status == 'pending')
                  <span class="badge bg-yellow-lt">Menunggu</span>
                @elseif($return->status == 'approved')
                  <span class="badge bg-green-lt">Disetujui</span>
                @else
                  <span class="badge bg-red-lt">Ditolak</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <!-- Create Return button for approved requests -->
    @if($request->status == 'approved' && auth()->id() == $request->requested_by && !$request->returns->where('status', 'pending')->count())
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Pengembalian Material</h3>
        <p>Jika ada sisa material yang tidak digunakan, Anda dapat mengembalikannya:</p>
        <a href="{{ route('admin.returns.create', ['request_id' => $request->id]) }}" class="btn btn-primary">
          <i class="ti ti-arrow-back-up me-1"></i> Buat Pengembalian
        </a>
      </div>
    </div>
    @endif
  </div>
</div>

@if(auth()->user()->can('approve-requests') && $request->status == 'pending')
<!-- Approve Modal -->
<div class="modal modal-blur fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Konfirmasi Persetujuan</div>
        <div>Apakah Anda yakin ingin menyetujui permintaan material ini?</div>
        <div class="mt-2">
          <div class="mb-1"><strong>Material:</strong> {{ $request->material->name }}</div>
          <div class="mb-1"><strong>Jumlah:</strong> {{ $request->quantity }} {{ $request->material->unit }}</div>
          <div class="mb-1"><strong>Peminta:</strong> {{ $request->requester->name }}</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('admin.requests.approve', $request) }}" method="POST">
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
      <form action="{{ route('admin.requests.reject', $request) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="modal-title">Konfirmasi Penolakan</div>
          <div>Apakah Anda yakin ingin menolak permintaan material ini?</div>
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