@extends('layouts.app')

@section('title', 'Riwayat Stok Saya')

@section('page-pretitle', 'Stok')
@section('page-title', 'Riwayat Stok Saya')

@section('content')
<div class="row mb-3">
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Total Dipakai</div>
        <div class="h1 mb-3 text-warning">{{ (float) $summary['total_used'] }}</div>
        <div class="text-muted">Jumlah material dipakai</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Total Dikembalikan</div>
        <div class="h1 mb-3 text-success">{{ (float) $summary['total_returned'] }}</div>
        <div class="text-muted">Pengembalian disetujui</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Total Catatan</div>
        <div class="h1 mb-3">{{ $summary['total_records'] }}</div>
        <div class="text-muted">Riwayat pergerakan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">Material Berbeda</div>
        <div class="h1 mb-3 text-info">{{ $summary['distinct_materials'] }}</div>
        <div class="text-muted">Jenis material</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="card-title">Riwayat Pemakaian & Pengembalian</h3>
      <div class="card-actions">
        <a href="{{ route('admin.stocks.index') }}" class="btn btn-sm btn-outline-secondary me-2">
          <i class="ti ti-arrow-left me-1"></i> Kembali ke Stok
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'category', 'type', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.stocks.my-history') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Cari Material</label>
            <input type="text" class="form-control" name="search" placeholder="Cari nama material..." value="{{ request('search') }}">
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

          <div class="col-md-2">
            <label class="form-label">Jenis</label>
            <select name="type" class="form-select">
              <option value="">Semua</option>
              <option value="pakai" {{ request('type') === 'pakai' ? 'selected' : '' }}>Pemakaian</option>
              <option value="kembali" {{ request('type') === 'kembali' ? 'selected' : '' }}>Pengembalian</option>
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
            <a href="{{ route('admin.stocks.my-history') }}" class="btn btn-outline-secondary">
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
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Material</th>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($history as $index => $item)
            <tr>
              <td>{{ ($history->currentPage() - 1) * $history->perPage() + $index + 1 }}</td>
              <td class="text-nowrap">{{ $item->date->format('d M Y H:i') }}</td>
              <td>
                @if($item->type === 'consumption')
                  <span class="badge bg-warning-lt"><i class="ti ti-arrow-right me-1"></i> Pemakaian</span>
                @else
                  <span class="badge bg-success-lt"><i class="ti ti-arrow-back-up me-1"></i> Pengembalian</span>
                @endif
              </td>
              <td><strong>{{ $item->material->name ?? '-' }}</strong></td>
              <td>
                @if($item->material && $item->material->category)
                  <span class="badge bg-blue-lt">{{ $item->material->category->name }}</span>
                @else
                  <span class="badge bg-gray-lt">Tanpa Kategori</span>
                @endif
              </td>
              <td class="text-nowrap">{{ (float) $item->quantity }} {{ $item->material->unit ?? '' }}</td>
              <td>
                @if($item->type === 'return')
                  @php
                    $statusBadge = match($item->status) {
                      'approved' => 'bg-success', 'pending' => 'bg-warning', 'rejected' => 'bg-danger', default => 'bg-secondary'
                    };
                    $statusLabel = match($item->status) {
                      'approved' => 'Disetujui', 'pending' => 'Menunggu', 'rejected' => 'Ditolak', default => ucfirst($item->status)
                    };
                  @endphp
                  <span class="badge {{ $statusBadge }}-lt">{{ $statusLabel }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $item->notes ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-history text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Belum ada riwayat</p>
                  <p class="empty-subtitle text-muted">
                    Anda belum memiliki riwayat pemakaian atau pengembalian material.
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
      Menampilkan <span>{{ $history->firstItem() ?? 0 }}</span> sampai <span>{{ $history->lastItem() ?? 0 }}</span> dari <span>{{ $history->total() }}</span> catatan
    </p>
    <div class="ms-auto">
      {{ $history->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>
@endsection
