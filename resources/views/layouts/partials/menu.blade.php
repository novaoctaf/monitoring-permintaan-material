<!-- Dashboard -->
<li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
  <a class="nav-link" href="{{ route('admin.dashboard') }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-home"></i>
    </span>
    <span class="nav-link-title">Dashboard</span>
  </a>
</li>

<!-- Inventory Management - Group for Materials and Categories -->
<li class="nav-item dropdown {{ request()->routeIs('admin.materials.*') || request()->routeIs('admin.categories.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.materials.*') || request()->routeIs('admin.categories.*') ? 'show' : '' }}" 
     href="#navbar-inventory" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.materials.*') || request()->routeIs('admin.categories.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-building-warehouse"></i>
    </span>
    <span class="nav-link-title">Inventory</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.materials.*') || request()->routeIs('admin.categories.*') ? 'show' : '' }}">
    <!-- Materials submenu -->
    <div class="dropend">
      <a class="dropdown-item dropdown-toggle {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}" 
         href="#sidebar-materials"
         data-bs-toggle="dropdown"
         data-bs-auto-close="false"
         role="button"
         aria-expanded="{{ request()->routeIs('admin.materials.*') ? 'true' : 'false' }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block me-1">
          <i class="ti ti-package"></i>
        </span>
        Material
      </a>
      <div class="dropdown-menu {{ request()->routeIs('admin.materials.*') ? 'show' : '' }}">
        <a class="dropdown-item {{ request()->routeIs('admin.materials.index') ? 'active' : '' }}"
          href="{{ route('admin.materials.index') }}">
          Daftar Material 
        </a>
        @can('create-inventory')
        <a class="dropdown-item {{ request()->routeIs('admin.materials.create') ? 'active' : '' }}"
          href="{{ route('admin.materials.create') }}">
          Tambah Material
        </a>
        @endcan
      </div>
    </div>

    <!-- Categories submenu -->
    <div class="dropend">
      <a class="dropdown-item dropdown-toggle {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
         href="#sidebar-categories"
         data-bs-toggle="dropdown"
         data-bs-auto-close="false"
         role="button"
         aria-expanded="{{ request()->routeIs('admin.categories.*') ? 'true' : 'false' }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block me-1">
          <i class="ti ti-category"></i>
        </span>
        Kategori
      </a>
      <div class="dropdown-menu {{ request()->routeIs('admin.categories.*') ? 'show' : '' }}">
        <a class="dropdown-item {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}"
          href="{{ route('admin.categories.index') }}">
          Daftar Kategori
        </a>
        @can('create-inventory')
        <a class="dropdown-item {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}"
          href="{{ route('admin.categories.create') }}">
          Tambah Kategori
        </a>
        @endcan
      </div>
    </div>
  </div>
</li>

<!-- Stock Management - Visible to all roles, but with different permissions -->
<li class="nav-item dropdown {{ request()->routeIs('admin.stocks.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.stocks.*') ? 'show' : '' }}" 
     href="#navbar-stocks" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.stocks.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-stack"></i>
    </span>
    <span class="nav-link-title">Stok</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.stocks.*') ? 'show' : '' }}">
    <a class="dropdown-item {{ request()->routeIs('admin.stocks.index') ? 'active' : '' }}"
      href="{{ route('admin.stocks.index') }}">
      Daftar Stok
    </a>
    @role('produksi')
    <a class="dropdown-item {{ request()->routeIs('admin.stocks.my-history') ? 'active' : '' }}"
      href="{{ route('admin.stocks.my-history') }}">
      Riwayat Stok Saya
    </a>
    @endrole
    @role('staff')
    <a class="dropdown-item {{ request()->routeIs('admin.stocks.adjust') ? 'active' : '' }}"
      href="{{ route('admin.stocks.adjust') }}">
      Penyesuaian Stok
    </a>
    @endrole
    @role('staff|store')
    <a class="dropdown-item {{ request()->routeIs('admin.stocks.index') && request('view') === 'produksi' ? 'active' : '' }}"
      href="{{ route('admin.stocks.index', ['view' => 'produksi']) }}">
      Stok Produksi
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.stocks.history') ? 'active' : '' }}"
      href="{{ route('admin.stocks.history') }}">
      Riwayat Stok
    </a>
    @endrole
  </div>
</li>

<!-- Material Requests - Visible to all, but produksi creates, store/staff approves -->
<li class="nav-item dropdown {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.requests.*') ? 'show' : '' }}" 
     href="#navbar-requests" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.requests.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-shopping-cart"></i>
    </span>
    <span class="nav-link-title">Permintaan Material</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.requests.*') ? 'show' : '' }}">
    <a class="dropdown-item {{ request()->routeIs('admin.requests.index') ? 'active' : '' }}"
      href="{{ route('admin.requests.index') }}">
      Daftar Permintaan
    </a>
    @can('create-requests')
    <a class="dropdown-item {{ request()->routeIs('admin.requests.create') ? 'active' : '' }}"
      href="{{ route('admin.requests.create') }}">
      Buat Permintaan
    </a>
    @endcan
    @can('approve-requests')
    <a class="dropdown-item {{ request()->routeIs('admin.requests.approvals') ? 'active' : '' }}"
      href="{{ route('admin.requests.approvals') }}">
      Persetujuan Permintaan
    </a>
    @endcan
  </div>
</li>

<!-- Material Returns - Produksi returns unused materials, store/staff approves -->
<li class="nav-item dropdown {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.returns.*') ? 'show' : '' }}" 
     href="#navbar-returns" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.returns.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-arrow-back-up"></i>
    </span>
    <span class="nav-link-title">Pengembalian Material</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.returns.*') ? 'show' : '' }}">
    <a class="dropdown-item {{ request()->routeIs('admin.returns.index') ? 'active' : '' }}"
      href="{{ route('admin.returns.index') }}">
      Daftar Pengembalian
    </a>
    @can('create-returns')
    <a class="dropdown-item {{ request()->routeIs('admin.returns.create') ? 'active' : '' }}"
      href="{{ route('admin.returns.create') }}">
      Buat Pengembalian
    </a>
    @endcan
    @can('approve-returns')
    <a class="dropdown-item {{ request()->routeIs('admin.returns.approvals') ? 'active' : '' }}"
      href="{{ route('admin.returns.approvals') }}">
      Persetujuan Pengembalian
    </a>
    @endcan
  </div>
</li>

<!-- Reports - Staff only -->
@role('staff')
<li class="nav-item dropdown {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" 
     href="#navbar-reports" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-chart-bar"></i>
    </span>
    <span class="nav-link-title">Laporan</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}">
    <a class="dropdown-item {{ request()->routeIs('admin.reports.stock') ? 'active' : '' }}"
      href="{{ route('admin.reports.stock') }}">
      <i class="ti ti-box me-1"></i> Laporan Stok
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.reports.requests') ? 'active' : '' }}"
      href="{{ route('admin.reports.requests') }}">
      <i class="ti ti-shopping-cart me-1"></i> Laporan Permintaan
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.reports.production.stock') ? 'active' : '' }}"
      href="{{ route('admin.reports.production.stock') }}">
      <i class="ti ti-stack me-1"></i> Laporan Stok Produksi
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.reports.returns') ? 'active' : '' }}"
      href="{{ route('admin.reports.returns') }}">
      <i class="ti ti-arrow-back-up me-1"></i> Laporan Pengembalian
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.reports.consumptions') ? 'active' : '' }}"
      href="{{ route('admin.reports.consumptions') }}">
      <i class="ti ti-chart-line me-1"></i> Laporan Pemakaian
    </a>
  </div>
</li>
@endrole

<!-- User Management - Only visible to staff (admin) role -->
@role('staff')
<li class="nav-item dropdown {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
  <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'show' : '' }}" 
     href="#navbar-auth" 
     data-bs-toggle="dropdown" 
     data-bs-auto-close="false"
     role="button" 
     aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }}">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <i class="ti ti-users"></i>
    </span>
    <span class="nav-link-title">Manajemen Akses</span>
  </a>
  <div class="dropdown-menu {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'show' : '' }}">
    <a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
      href="{{ route('admin.users.index') }}">
      Pengguna
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
      href="{{ route('admin.roles.index') }}">
      Peran
    </a>
    <a class="dropdown-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
      href="{{ route('admin.permissions.index') }}">
      Izin
    </a>
  </div>
</li>
@endrole

