@extends('layouts.app')

@section('title', 'Detail Material')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Detail Material')

@section('page-actions')
  <div class="btn-list">
    @can('edit-inventory')
    <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-warning d-none d-sm-inline-block">
      <i class="ti ti-edit"></i> Edit
    </a>
    <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-warning d-sm-none">
      <i class="ti ti-edit"></i>
    </a>
    @endcan
    
    @can('edit-stocks')
    <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="btn btn-info d-none d-sm-inline-block">
      <i class="ti ti-stack"></i> Sesuaikan Stok
    </a>
    <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="btn btn-info d-sm-none">
      <i class="ti ti-stack"></i>
    </a>
    @endcan
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card">
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
          
          @role('staff|store')
          <div class="datagrid-item">
            <div class="datagrid-title">Stok</div>
            <div class="datagrid-content">
              @php
                $stockQty = (float) $material->stock->quantity ?? 0;
                $badgeClass = $stockQty <= 0 ? 'bg-danger' : ($stockQty <= 10 ? 'bg-warning' : 'bg-success');
              @endphp
              <span class="badge {{ $badgeClass }}-lt">{{ $stockQty }} {{ $material->unit }}</span>
            </div>
          </div>
          @endrole
          
          <div class="datagrid-item">
            <div class="datagrid-title">Terakhir Diperbarui</div>
            <div class="datagrid-content">{{ $material->updated_at->format('d M Y H:i') }}</div>
          </div>
          
          <div class="datagrid-item col-span-2">
            <div class="datagrid-title">Deskripsi</div>
            <div class="datagrid-content">{{ $material->description ?: 'Tidak ada deskripsi' }}</div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="d-flex">
          <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Kembali ke Daftar
          </a>
          
          @can('delete-inventory')
          <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="ti ti-trash"></i> Hapus
          </button>
          @endcan
        </div>
      </div>
    </div>
  </div>
  
  @role('staff|store')
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Ringkasan Stok</h3>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="me-3">
            <span class="avatar avatar-xl bg-teal-lt">
              <i class="ti ti-box fs-1"></i>
            </span>
          </div>
          <div>
            <h3 class="mb-0">
              <a href="{{ route('admin.stocks.show', $material) }}" class="text-reset">
                {{ (float) $material->stock->quantity ?? 0 }} <small>{{ $material->unit }}</small>
              </a>
            </h3>
            <div class="text-muted">Stok saat ini</div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="me-2">
                <span class="avatar bg-green-lt">
                  <i class="ti ti-arrow-up"></i>
                </span>
              </div>
              <div>
                <div class="text-secondary">Total Masuk</div>
                <div class="fw-medium">
                  {{ \App\Models\StockAdjustment::where('material_id', $material->id)
                      ->where('adjustment_quantity', '>', 0)
                      ->sum('adjustment_quantity') }} {{ $material->unit }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="d-flex align-items-center">
              <div class="me-2">
                <span class="avatar bg-red-lt">
                  <i class="ti ti-arrow-down"></i>
                </span>
              </div>
              <div>
                <div class="text-secondary">Total Keluar</div>
                <div class="fw-medium">
                  {{ abs(\App\Models\StockAdjustment::where('material_id', $material->id)
                      ->where('adjustment_quantity', '<', 0)
                      ->sum('adjustment_quantity')) }} {{ $material->unit }}
                </div>
              </div>
            </div>
          </div>
        </div>

        @php
          $stockQty = $material->stock->quantity ?? 0;
          $statusClass = $stockQty <= 0 ? 'bg-red-lt' : ($stockQty <= 10 ? 'bg-yellow-lt' : 'bg-green-lt');
          $statusText = $stockQty <= 0 ? 'Habis' : ($stockQty <= 10 ? 'Menipis' : 'Tersedia');
        @endphp
        
        <div class="mt-4">
          <div class="d-flex align-items-center justify-content-between">
            <span class="text-secondary">Status:</span>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
          </div>
        </div>
        
        @can('edit-stocks')
          <div class="mt-4">
            <a href="{{ route('admin.stocks.adjust', ['material_id' => $material->id]) }}" class="btn btn-outline-primary w-100">
              <i class="ti ti-stack me-1"></i> Sesuaikan Stok
            </a>
          </div>
        @endcan
      </div>
    </div>
    
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Aktivitas Terkini</h3>
        @if($recentActivities->count() > 0)
          <div class="card-actions">
            <a href="{{ route('admin.stocks.history', ['search' => $material->name]) }}" class="btn btn-sm btn-outline-primary">
              Lihat Semua
            </a>
          </div>
        @endif
      </div>
      <div class="list-group list-group-flush">
        @forelse($recentActivities as $activity)
          <div class="list-group-item">
            <div class="row align-items-center">
              <div class="col-auto">
                @if($activity->adjustment_quantity > 0)
                  <span class="avatar bg-green-lt">
                    <i class="ti ti-arrow-up"></i>
                  </span>
                @else
                  <span class="avatar bg-red-lt">
                    <i class="ti ti-arrow-down"></i>
                  </span>
                @endif
              </div>
              <div class="col">
                <div class="d-flex align-items-center">
                  <div class="fw-bold">
                    {{ $activity->adjustment_quantity > 0 ? '+' : '' }}{{ $activity->adjustment_quantity }} {{ $material->unit }}
                  </div>
                  <span class="ms-2 badge {{ $activity->type === 'manual' ? 'bg-blue-lt' : ($activity->type === 'request' ? 'bg-green-lt' : 'bg-purple-lt') }}">
                    {{ $activity->type === 'manual' ? 'Manual' : ($activity->type === 'request' ? 'Permintaan' : 'Pengembalian') }}
                  </span>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1">
                  <div>
                    <span class="text-secondary">{{ $activity->user->name }}</span>
                  </div>
                  <div>
                    <i class="ti ti-clock me-1 opacity-70"></i> {{ $activity->created_at->diffForHumans() }}
                  </div>
                </div>
                @if($activity->notes)
                  <div class="text-muted border-start ps-2 mt-1 text-truncate" title="{{ $activity->notes }}">
                    {{ Str::limit($activity->notes, 50) }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="list-group-item">
            <div class="row">
              <div class="col text-center py-3">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-history text-muted" style="font-size: 2rem"></i>
                  </div>
                  <p class="empty-title">Belum ada riwayat aktivitas</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada penyesuaian stok yang tercatat untuk material ini.
                  </p>
                </div>
              </div>
            </div>
          </div>
        @endforelse
      </div>
      @if($recentActivities->count() > 0)
        <div class="card-footer text-end">
          <a href="{{ route('admin.stocks.history', ['search' => $material->name]) }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-list"></i> Lihat Semua Aktivitas
          </a>
        </div>
      @endif
    </div>
  </div>
  @endrole
</div>

@can('delete-inventory')
<!-- Delete Confirmation Modal -->
<div class="modal modal-blur fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Konfirmasi Hapus</div>
        <div>Apakah Anda yakin ingin menghapus material <strong>{{ $material->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('admin.materials.destroy', $material) }}" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Hapus Permanen</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endcan

@endsection