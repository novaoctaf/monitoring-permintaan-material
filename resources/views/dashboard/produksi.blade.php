@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-pretitle', 'Overview')
@section('page-title', 'Dashboard Produksi')

@section('content')
  {{-- Stats Cards --}}
  <div class="row">
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-warning text-white avatar">
                <i class="ti ti-hourglass"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $myPendingRequests }}
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
              <span class="bg-success text-white avatar">
                <i class="ti ti-check"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                {{ $myApprovedRequests }}
              </div>
              <div class="text-muted">
                Permintaan Disetujui
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
                {{ $myPendingReturns }}
              </div>
              <div class="text-muted">
                Pengembalian Menunggu
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
  </div>
  
  {{-- Charts Row --}}
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Aktivitas Permintaan & Pengembalian Saya</h3>
        </div>
        <div class="card-body">
          <div id="my-activity-chart"></div>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Status Permintaan Saya</h3>
        </div>
        <div class="card-body">
          <div id="my-request-status-chart"></div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-3">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Material yang Sering Saya Minta</h3>
        </div>
        <div class="card-body">
          <div id="my-top-materials-chart"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Activities Table --}}
  <div class="row mt-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Permintaan Material Terbaru Saya</h3>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>No. Permintaan</th>
                <th>Material</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentRequests as $request)
                <tr>
                  <td class="text-nowrap">{{ $request->request_number }}</td>
                  <td>{{ $request->material->name }}</td>
                  <td class="text-nowrap">{{ $request->quantity }} {{ $request->material->unit }}</td>
                  <td class="text-nowrap">{{ $request->created_at->format('d M Y') }}</td>
                  <td>
                    @if($request->status == 'pending')
                      <span class="badge bg-yellow-lt">Menunggu</span>
                    @elseif($request->status == 'approved')
                      <span class="badge bg-green-lt">Disetujui</span>
                    @else
                      <span class="badge bg-red-lt">Ditolak</span>
                    @endif
                  </td>
                  <td class="text-nowrap">
                    <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    @if($request->status == 'approved')
                      <a href="{{ route('admin.returns.create', ['request_id' => $request->id]) }}" class="btn btn-sm btn-info">Kembalikan</a>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Tidak ada permintaan terbaru</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <div class="d-flex">
            <a href="{{ route('admin.requests.create') }}" class="btn btn-primary ms-auto">Buat Permintaan Baru</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // My Activity Chart
    var myActivityOptions = {
      series: [{
        name: 'Permintaan',
        data: @json($myRequestHistory->pluck('requests'))
      }, {
        name: 'Pengembalian',
        data: @json($myRequestHistory->pluck('returns'))
      }],
      chart: {
        type: 'line',
        height: 300,
        toolbar: {
          show: false
        }
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      xaxis: {
        categories: @json($myRequestHistory->pluck('date')),
      },
      legend: {
        position: 'top'
      },
      markers: {
        size: 4
      }
    };
    new ApexCharts(document.querySelector("#my-activity-chart"), myActivityOptions).render();

    // My Request Status Chart
    var myRequestStatusOptions = {
      series: [@json($myRequestStats['pending']), @json($myRequestStats['approved']), @json($myRequestStats['rejected'])],
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
    new ApexCharts(document.querySelector("#my-request-status-chart"), myRequestStatusOptions).render();

    // My Top Materials Chart
    var myTopMaterialsOptions = {
      series: [{
        name: 'Total Permintaan',
        data: @json($myTopMaterials->pluck('total'))
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
        categories: @json($myTopMaterials->pluck('material.name')),
      }
    };
    new ApexCharts(document.querySelector("#my-top-materials-chart"), myTopMaterialsOptions).render();
  });
</script>
@endpush