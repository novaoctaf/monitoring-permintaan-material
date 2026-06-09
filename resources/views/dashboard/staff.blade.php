@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-pretitle', 'Overview')
@section('page-title', 'Dashboard Admin')

@section('content')
  {{-- Stats Cards --}}
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
                <i class="ti ti-users"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $totalUsers }}
              </div>
              <div class="text-muted">
                Total Pengguna
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  {{-- Charts Row --}}
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Aktivitas Permintaan & Pengembalian</h3>
        </div>
        <div class="card-body">
          <div id="daily-activity-chart"></div>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Distribusi Status Permintaan</h3>
        </div>
        <div class="card-body">
          @if($requestStats['pending'] > 0 || $requestStats['approved'] > 0 || $requestStats['rejected'] > 0)
            <div id="request-status-chart"></div>
          @else
            <div class="empty">
              <div class="empty-icon">
                <i class="ti ti-chart-pie text-muted" style="font-size: 3rem"></i>
              </div>
              <p class="empty-title">Tidak ada data</p>
              <p class="empty-subtitle text-muted">
                Belum ada permintaan material yang tercatat dalam sistem.
              </p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Material berdasarkan Kategori</h3>
        </div>
        <div class="card-body">
          @if($materialsByCategory->isNotEmpty())
            <div id="materials-by-category-chart"></div>
          @else
            <div class="empty">
              <div class="empty-icon">
                <i class="ti ti-category text-muted" style="font-size: 3rem"></i>
              </div>
              <p class="empty-title">Tidak ada data</p>
              <p class="empty-subtitle text-muted">
                Belum ada material yang terdaftar dalam sistem.
              </p>
            </div>
          @endif
        </div>
      </div>
    </div>
    
    {{-- Recent Activities Tables --}}
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
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">Tidak ada permintaan terbaru</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <a href="{{ route('admin.requests.index') }}" class="btn btn-primary">Lihat Semua</a>
        </div>
      </div>
      
      <div class="card mt-3">
        <div class="card-header">
          <h3 class="card-title">Pengembalian Material Terbaru</h3>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>No. Pengembalian</th>
                <th>Material</th>
                <th>Pengembalian</th>
                <th>Status</th>
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
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">Tidak ada pengembalian terbaru</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <a href="{{ route('admin.returns.index') }}" class="btn btn-primary">Lihat Semua</a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Daily Activity Chart
    var dailyActivityOptions = {
      series: [{
        name: 'Permintaan',
        data: @json($dailyStats->pluck('requests'))
      }, {
        name: 'Pengembalian',
        data: @json($dailyStats->pluck('returns'))
      }],
      chart: {
        type: 'area',
        height: 300,
        stacked: true,
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth'
      },
      xaxis: {
        categories: @json($dailyStats->pluck('date')),
      },
      legend: {
        position: 'top'
      },
      fill: {
        opacity: 0.8
      }
    };
    new ApexCharts(document.querySelector("#daily-activity-chart"), dailyActivityOptions).render();

    // Request Status Distribution Chart
    @if($requestStats['pending'] > 0 || $requestStats['approved'] > 0 || $requestStats['rejected'] > 0)
      var requestStatusOptions = {
        series: [@json($requestStats['pending']), @json($requestStats['approved']), @json($requestStats['rejected'])],
        chart: {
          type: 'donut',
          height: 300
        },
        labels: ['Menunggu', 'Disetujui', 'Ditolak'],
        colors: ['#fab005', '#2fb344', '#d63939'],
        legend: {
          position: 'bottom'
        }
      };
      new ApexCharts(document.querySelector("#request-status-chart"), requestStatusOptions).render();
    @endif

    // Materials by Category Chart
    @if($materialsByCategory->isNotEmpty())
      var materialsByCategoryOptions = {
        series: @json($materialsByCategory->pluck('total')),
        chart: {
          type: 'pie',
          height: 300
        },
        labels: @json($materialsByCategory->pluck('name')),
        legend: {
          position: 'bottom'
        }
      };
      new ApexCharts(document.querySelector("#materials-by-category-chart"), materialsByCategoryOptions).render();
    @endif
  });
</script>
@endpush