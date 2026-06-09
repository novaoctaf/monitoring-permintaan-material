@extends('layouts.app')

@section('title', 'Detail Stok')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Detail Stok Material Gudang')

@section('page-actions')
  <div class="btn-list">
    @can('edit-stocks')
    <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="btn btn-primary d-none d-sm-inline-block">
      <i class="ti ti-stack"></i> Sesuaikan Stok
    </a>
    <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="btn btn-primary d-sm-none">
      <i class="ti ti-stack"></i>
    </a>
    @endcan
    <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-3">
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Informasi Material</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Nama Material</div>
            <div class="datagrid-content fw-bold">{{ $material->name }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Kategori</div>
            <div class="datagrid-content">
              @if($material->category)
                <span class="badge bg-blue-lt">{{ $material->category->name }}</span>
              @else
                <span class="badge bg-gray-lt">Tanpa Kategori</span>
              @endif
            </div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Satuan</div>
            <div class="datagrid-content">{{ $material->unit }}</div>
          </div>
          
          <div class="datagrid-item">
            <div class="datagrid-title">Stok Saat Ini</div>
            <div class="datagrid-content">
              @php
                $stockQty = $stock->quantity ?? 0;
                $badgeClass = $stockQty <= 0 ? 'bg-danger' : ($stockQty <= 10 ? 'bg-warning' : 'bg-success');
              @endphp
              <span class="badge {{ $badgeClass }}-lt">{{ (float) $stockQty }} {{ $material->unit }}</span>
            </div>
          </div>

          @if($material->description)
          <div class="datagrid-item">
            <div class="datagrid-title">Deskripsi</div>
            <div class="datagrid-content">{{ $material->description }}</div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Statistik Stok</h3>
      </div>
      <div class="card-body">
        <div class="row row-cards">
          <div class="col-12">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-blue text-white avatar">
                      <i class="ti ti-stack"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">
                      {{ $adjustments->count() }}
                    </div>
                    <div class="text-muted">
                      Total Penyesuaian
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-9">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Riwayat Penyesuaian Stok</h3>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Penyesuai</th>
              <th>Sebelum</th>
              <th>Perubahan</th>
              <th>Sesudah</th>
              <th>Tipe</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($adjustments as $adjustment)
              <tr>
                <td class="text-nowrap">{{ $adjustment->created_at->format('d M Y H:i') }}</td>
                <td>{{ $adjustment->user->name }}</td>
                <td>{{ $adjustment->quantity_before }} {{ $material->unit }}</td>
                <td>
                  @if($adjustment->adjustment_quantity > 0)
                    <span class="text-success">+{{ $adjustment->adjustment_quantity }}</span>
                  @else
                    <span class="text-danger">{{ $adjustment->adjustment_quantity }}</span>
                  @endif
                </td>
                <td>{{ $adjustment->quantity_after }} {{ $material->unit }}</td>
                <td>
                  @if($adjustment->type === 'manual')
                    <span class="badge bg-blue-lt">Manual</span>
                  @elseif($adjustment->type === 'request')
                    <span class="badge bg-green-lt">Permintaan</span>
                  @else
                    <span class="badge bg-purple-lt">Pengembalian</span>
                  @endif
                </td>
                <td>{{ $adjustment->notes ?: '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="empty">
                    <div class="empty-icon">
                      <i class="ti ti-history text-muted" style="font-size: 3rem"></i>
                    </div>
                    <p class="empty-title">Tidak ada riwayat</p>
                    <p class="empty-subtitle text-muted">
                      Belum ada penyesuaian stok yang tercatat untuk material ini.
                    </p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($adjustments->hasPages())
      <div class="card-footer d-flex align-items-center">
        {{ $adjustments->links('pagination.tabler') }}
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toast after 4 seconds
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