@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('page-pretitle', 'Audit Trail')
@section('page-title', 'Log Aktivitas')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex align-items-center justify-content-between w-100">
      <h3 class="card-title">Riwayat Aktivitas Sistem</h3>
      <div class="card-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
      </div>
    </div>
  </div>

  <div class="collapse {{ request()->hasAny(['search', 'event', 'log_name', 'causer', 'date_from', 'date_to']) ? 'show' : '' }}" id="filter-collapse">
    <div class="card-body border-bottom">
      <form action="{{ route('admin.activity-logs.index') }}" method="GET">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Pencarian</label>
            <input type="text" class="form-control" name="search" placeholder="Cari deskripsi..." value="{{ request('search') }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">Modul</label>
            <select name="log_name" class="form-select">
              <option value="">Semua Modul</option>
              @foreach($logNames as $name)
                <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Aktivitas</label>
            <select name="event" class="form-select">
              <option value="">Semua Aktivitas</option>
              @foreach($events as $event)
                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Pengguna</label>
            <select name="causer" class="form-select">
              <option value="">Semua Pengguna</option>
              @foreach($causers as $user)
                <option value="{{ $user->id }}" {{ request('causer') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
              @endforeach
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
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
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
            <th>Waktu</th>
            <th>Pengguna</th>
            <th>Modul</th>
            <th>Aktivitas</th>
            <th>Deskripsi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td class="text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
              <td>{{ $log->causer->name ?? 'Sistem' }}</td>
              <td>@if($log->log_name)<span class="badge bg-blue-lt">{{ ucfirst($log->log_name) }}</span>@else - @endif</td>
              <td><span class="badge bg-{{ $log->event_color }}-lt"><i class="ti {{ $log->event_icon }} me-1"></i>{{ ucfirst($log->event) }}</span></td>
              <td>{{ $log->description }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-history text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada aktivitas</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada aktivitas tercatat atau tidak ada yang sesuai dengan filter yang dipilih.
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
      Menampilkan <span>{{ $logs->firstItem() ?? 0 }}</span> sampai <span>{{ $logs->lastItem() ?? 0 }}</span> dari <span>{{ $logs->total() }}</span> aktivitas
    </p>
    <div class="ms-auto">
      {{ $logs->onEachSide(1)->appends(request()->query())->links('pagination.tabler') }}
    </div>
  </div>
</div>
@endsection
