@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-pretitle', 'Overview')
@section('page-title', 'Dashboard Karyawan Operasional')

@section('content')
  <div class="row">
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-primary text-white avatar">
                <i class="ti ti-package"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $totalMaterials }}
              </div>
              <div class="text-muted">
                Total Material
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-danger text-white avatar">
                <i class="ti ti-alert-triangle"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $lowStockCount }}
              </div>
              <div class="text-muted">
                Stok Menipis
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-warning text-white avatar">
                <i class="ti ti-shopping-cart"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $pendingRequests }}
              </div>
              <div class="text-muted">
                Permintaan Menunggu
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-info text-white avatar">
                <i class="ti ti-arrow-back-up"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $pendingReturns }}
              </div>
              <div class="text-muted">
                Pengembalian Menunggu
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Pergerakan Material</h3>
        </div>
        <div class="card-body">
          <div id="material-movement-chart"></div>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Material Stok Menipis</h3>
          <div class="card-options">
            <span class="badge bg-red me-1">Habis: {{ $emptyCount }}</span>
            <span class="badge bg-orange">Kritis: {{ $criticalCount }}</span>
          </div>
        </div>
        @php
          $alertMaterials = $lowStockMaterials->filter(fn($m) => in_array($m['status'], ['habis', 'kritis']))->values();
        @endphp
        @if($alertMaterials->isNotEmpty())
        <div class="table-responsive">
          <table class="table card-table table-vcenter mb-0">
            <thead>
              <tr>
                <th>Material</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Batas Kritis</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($alertMaterials as $mat)
              <tr>
                <td class="fw-medium">{{ $mat['name'] }}</td>
                <td class="text-center">
                  {{ $mat['quantity'] }}
                  @if($mat['unit']) <small class="text-muted">{{ $mat['unit'] }}</small> @endif
                </td>
                <td class="text-center text-muted">
                  {{ $mat['threshold'] !== null ? $mat['threshold'] : '-' }}
                </td>
                <td class="text-center">
                  @if($mat['status'] === 'habis')
                    <span class="badge bg-red">Habis</span>
                  @else
                    <span class="badge bg-orange">Kritis</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="card-body">
          <div class="empty">
            <div class="empty-icon">
              <i class="ti ti-circle-check text-green" style="font-size: 2.5rem;"></i>
            </div>
            <p class="empty-title">Tidak ada material kritis</p>
            <p class="empty-subtitle text-muted">
              Semua material masih dalam batas aman.
            </p>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
  
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Material Terbanyak Diminta</h3>
        </div>
        <div class="card-body">
          <div id="top-materials-chart"></div>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Permintaan Material Terbaru</h3>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>No. Permintaan</th>
                <th>Material</th>
                <th>Peminta</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentRequests as $request)
                <tr>
                  <td>{{ $request->request_number }}</td>
                  <td>{{ $request->material->name }}</td>
                  <td>{{ $request->requester->name }}</td>
                  <td>
                    @if($request->status == 'pending')
                      <span class="badge bg-yellow-lt">Menunggu</span>
                    @elseif($request->status == 'approved')
                      <span class="badge bg-green-lt">Disetujui</span>
                    @else
                      <span class="badge bg-red-lt">Ditolak</span>
                    @endif
                  </td>
                  <td>
                    @if($request->status == 'pending')
                      <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-sm btn-primary">Tinjau</a>
                    @else
                      <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada permintaan terbaru</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <a href="{{ route('admin.requests.approvals') }}" class="btn btn-primary">Lihat Semua Permintaan</a>
        </div>
      </div>
    </div>
    
    <div class="row mt-3">
    <div class="col-12 col-md-8">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Pengembalian Material Terbaru</h3>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>No. Pengembalian</th>
                <th>Material</th>
                <th>Pengembali</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentReturns as $return)
                <tr>
                  <td>{{ $return->return_number }}</td>
                  <td>{{ $return->request->material->name }}</td>
                  <td>{{ $return->returner->name }}</td>
                  <td>
                    @if($return->status == 'pending')
                      <span class="badge bg-yellow-lt">Menunggu</span>
                    @elseif($return->status == 'approved')
                      <span class="badge bg-green-lt">Disetujui</span>
                    @else
                      <span class="badge bg-red-lt">Ditolak</span>
                    @endif
                  </td>
                  <td>
                    @if($return->status == 'pending')
                      <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-sm btn-primary">Tinjau</a>
                    @else
                      <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Tidak ada pengembalian terbaru</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <a href="{{ route('admin.returns.approvals') }}" class="btn btn-primary">Lihat Semua Pengembalian</a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Material Movement Chart
    var materialMovementOptions = {
      series: [{
        name: 'Material Masuk',
        data: @json($materialMovements->pluck('in'))
      }, {
        name: 'Material Keluar',
        data: @json($materialMovements->pluck('out'))
      }],
      chart: {
        type: 'bar',
        height: 300,
        stacked: true,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
        },
      },
      xaxis: {
        categories: @json($materialMovements->pluck('date')),
      },
      legend: {
        position: 'top'
      },
      fill: {
        opacity: 1
      }
    };
    new ApexCharts(document.querySelector("#material-movement-chart"), materialMovementOptions).render();

    // Top Materials Chart
    var topMaterialsOptions = {
      series: [{
        name: 'Total Permintaan',
        data: @json($topMaterials->pluck('total'))
      }],
      chart: {
        type: 'bar',
        height: 300,
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          distributed: true,
        }
      },
      colors: ['#206bc4'],
      dataLabels: {
        enabled: true
      },
      xaxis: {
        categories: @json($topMaterials->pluck('material.name')),
      }
    };
    new ApexCharts(document.querySelector("#top-materials-chart"), topMaterialsOptions).render();
  });
</script>
@endpush