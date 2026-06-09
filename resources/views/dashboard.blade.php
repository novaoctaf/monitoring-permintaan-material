@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-pretitle', 'Akun')
@section('page-title', 'Dashboard')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Selamat Datang di PT. XYZ Sistem Monitoring</h3>
      </div>
      <div class="card-body">
        @if (session('status'))
          <div class="alert alert-success" role="alert">
            {{ session('status') }}
          </div>
        @endif

        <div class="text-center py-4">
          <div class="mb-3">
            <span class="avatar avatar-xl mb-3 bg-blue-lt">
              <i class="ti ti-user-check fs-1"></i>
            </span>
          </div>
          <h2>Akun Anda Sedang Diverifikasi</h2>
          <p class="text-muted">
            Terima kasih telah mendaftar di sistem manajemen inventaris PT. XYZ. 
            Akun Anda sudah berhasil dibuat, namun saat ini sedang menunggu persetujuan 
            dan penetapan peran dari administrator sistem.
          </p>
        </div>
        
        <div class="alert alert-info">
          <div class="d-flex">
            <div>
              <i class="ti ti-info-circle icon alert-icon"></i>
            </div>
            <div>
              <h4>Apa yang terjadi selanjutnya?</h4>
              <p>Admin sistem akan menetapkan peran yang sesuai dengan tanggung jawab Anda. 
              Setelah peran diberikan, Anda akan memiliki akses penuh ke fitur-fitur yang 
              relevan dengan pekerjaan Anda.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <div class="text-center">
          <h4 class="text-muted mb-3">Peran yang tersedia dalam sistem:</h4>
          <div class="row g-3 justify-content-center">
            <div class="col-md-4">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-blue-lt me-2">
                      <i class="ti ti-user-shield"></i>
                    </span>
                    <h4 class="mb-0">Staff</h4>
                  </div>
                  <p class="text-muted small">Mengelola seluruh sistem, termasuk manajemen pengguna, material, dan laporan.</p>
                </div>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-green-lt me-2">
                      <i class="ti ti-building-warehouse"></i>
                    </span>
                    <h4 class="mb-0">Gudang</h4>
                  </div>
                  <p class="text-muted small">Mengelola stok material, menyetujui permintaan, dan menangani pengembalian.</p>
                </div>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="card card-sm">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    <span class="avatar avatar-sm bg-orange-lt me-2">
                      <i class="ti ti-building-factory"></i>
                    </span>
                    <h4 class="mb-0">Produksi</h4>
                  </div>
                  <p class="text-muted small">Membuat permintaan material dan mengelola pengembalian material yang tidak terpakai.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card mt-3">
      <div class="card-body">
        <div class="d-flex">
          <div class="me-3">
            <span class="avatar bg-yellow-lt">
              <i class="ti ti-mail-question"></i>
            </span>
          </div>
          <div>
            <h3 class="card-title">Butuh Bantuan?</h3>
            <p class="text-muted">
              Jika Anda memiliki pertanyaan tentang akun atau peran Anda, silakan hubungi 
              administrator sistem di <strong>admin@ptxyz.com</strong> atau hubungi
              bagian IT di ekstensi <strong>08123456789</strong>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
