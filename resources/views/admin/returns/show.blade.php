@extends('layouts.app')

@section('title', 'Detail Pengembalian Material')

@section('page-pretitle', 'Pengembalian')
@section('page-title', 'Detail Pengembalian')

@section('page-actions')
  <div class="btn-list">
    @if(auth()->user()->can('approve-returns') && !auth()->user()->hasRole('store') && $return->status == 'pending')
    <div class="btn-group">
      <button type="button" class="btn btn-success d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#approveModal">
        <i class="ti ti-check"></i> Setujui
      </button>
      <button type="button" class="btn btn-danger d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="ti ti-x"></i> Tolak
      </button>
    </div>
    @endif

    {{-- Serah terima: produksi mengembalikan barang --}}
    @role('produksi')
      @if($return->status == 'approved' && !$return->handed_over_at && $return->returned_by == auth()->id())
      <button type="button" class="btn btn-cyan js-confirm-action"
              data-action="{{ route('admin.returns.handover', $return) }}"
              data-title="Serahkan Barang · {{ $return->return_number }}"
              data-note="Barang dikembalikan ke store."
              data-body="<div class='mb-1'><strong>Material:</strong> {{ $return->request->material->name }}</div><div class='mb-1'><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div><div class='mb-1'><strong>No. Permintaan:</strong> {{ $return->request->request_number }}</div>"
              data-confirm-label="Serahkan" data-confirm-class="btn-cyan">
        <i class="ti ti-truck-delivery"></i> Serahkan Barang
      </button>
      @endif
    @endrole

    {{-- Serah terima: store menerima barang --}}
    @role('store')
      @if($return->status == 'approved' && $return->handed_over_at && !$return->received_at)
      <button type="button" class="btn btn-teal js-confirm-action"
              data-action="{{ route('admin.returns.receive', $return) }}"
              data-title="Terima Barang · {{ $return->return_number }}"
              data-note="Stok utama akan bertambah."
              data-body="<div class='mb-1'><strong>Material:</strong> {{ $return->request->material->name }}</div><div class='mb-1'><strong>Jumlah:</strong> {{ $return->quantity }} {{ $return->request->material->unit }}</div><div class='mb-1'><strong>Dikembalikan oleh:</strong> {{ $return->returner->name }}</div>"
              data-confirm-label="Terima" data-confirm-class="btn-teal">
        <i class="ti ti-package-import"></i> Terima Barang
      </button>
      @endif
    @endrole

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

          @if($return->status == 'approved')
          <div class="datagrid-item">
            <div class="datagrid-title">Penyerahan Barang</div>
            <div class="datagrid-content">
              @if($return->handed_over_at)
                Oleh {{ $return->handedOverBy->name ?? '-' }} &middot; {{ $return->handed_over_at->format('d M Y H:i') }}
              @else
                <span class="text-muted">Menunggu penyerahan oleh produksi</span>
              @endif
            </div>
          </div>

          <div class="datagrid-item">
            <div class="datagrid-title">Penerimaan Barang</div>
            <div class="datagrid-content">
              @if($return->received_at)
                Oleh {{ $return->receivedBy->name ?? '-' }} &middot; {{ $return->received_at->format('d M Y H:i') }}
              @elseif($return->handed_over_at)
                <span class="text-muted">Menunggu konfirmasi penerimaan oleh store</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </div>
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

          @if($return->status != 'rejected' && !$return->received_at)
          <div class="datagrid-item">
            <div class="datagrid-title">Stok Setelah Diterima</div>
            <div class="datagrid-content">
              <span class="badge bg-blue-lt">{{ $stockQty + $return->quantity }} {{ $return->request->material->unit }}</span>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Audit Trail / Riwayat Aktivitas -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Riwayat Aktivitas</h3>
      </div>
      <div class="card-body">
        @php $fieldLabels = \App\Models\ReturnMaterial::activityFieldLabels(); @endphp
        @if($return->activities->isEmpty())
          <p class="text-secondary mb-0">Belum ada aktivitas tercatat.</p>
        @else
          <ul class="timeline">
          @foreach($return->activities as $activity)
            <li class="timeline-event">
              <div class="timeline-event-icon bg-{{ $activity->event_color }}-lt">
                <i class="ti {{ $activity->event_icon }}"></i>
              </div>
              <div class="card timeline-event-card">
                <div class="card-body">
                  <div class="text-secondary float-end">{{ $activity->created_at->format('d M Y H:i') }}</div>
                  <h4 class="mb-1">{{ $activity->description }}</h4>
                  <p class="text-secondary mb-1">
                    <i class="ti ti-user me-1"></i>{{ $activity->causer->name ?? 'Sistem' }}
                  </p>
                  @if($activity->event === 'updated' && !empty($activity->properties['attributes']))
                    <div class="mt-2">
                      @foreach($activity->properties['attributes'] as $field => $newValue)
                        @if(isset($fieldLabels[$field]))
                          <div class="small text-secondary">
                            <strong>{{ $fieldLabels[$field] }}:</strong>
                            <span class="text-decoration-line-through">{{ $activity->properties['old'][$field] ?? '-' }}</span>
                            <i class="ti ti-arrow-right"></i>
                            {{ $newValue ?? '-' }}
                          </div>
                        @endif
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
            </li>
          @endforeach
          </ul>
        @endif
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
  });
</script>
@endpush