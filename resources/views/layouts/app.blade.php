<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tabler CSS from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.2.0/dist/css/tabler.min.css" />
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css">

    <!-- Inter font -->
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Tom Select CSS (enhanced dropdowns) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">

    <!-- ApexCharts CSS -->
    
    @stack('styles')

    <style>
      /* Let action dropdowns inside tables overflow the scroll container
         instead of being clipped by it (desktop where tables fit). */
      @media (min-width: 768px) {
        .table-responsive {
          overflow: visible;
        }
      }

      /* Tom Select: pastikan dropdown solid & di atas elemen lain (tidak tembus). */
      .ts-wrapper { display: block; }
      .ts-control {
        min-height: calc(1.4285714em + 0.875rem + 2px);
        background: var(--tblr-bg-forms, #fff);
        border-color: var(--tblr-border-color, #dee2e6);
      }
      .ts-dropdown {
        z-index: 1060;
        background: var(--tblr-bg-surface, #fff);
        border: 1px solid var(--tblr-border-color, #dee2e6);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        margin-top: 2px;
      }
      .ts-dropdown .active {
        background: var(--tblr-primary, #206bc4);
        color: #fff;
      }
      /* Badge notifikasi sidebar tidak melewati tepi. */
      .navbar-vertical .nav-link { align-items: center; }
    </style>
</head>
<body>
    <!-- Theme Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.2.0/dist/js/tabler.min.js"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tom Select JS (enhanced dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
      // Sulap semua <select.form-select> jadi dropdown yang bisa dicari.
      // Lewati select yang menandai dirinya dengan .no-tomselect.
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select.form-select:not(.no-tomselect)').forEach(function (el) {
          if (el.tomselect) return;
          new TomSelect(el, {
            placeholder: el.querySelector('option[value=""]')?.textContent || 'Pilih...',
            searchField: ['text'],
            maxOptions: null,
            controlInput: el.options.length > 8 ? undefined : null, // sembunyikan search box bila opsi sedikit
          });
        });
      });
    </script>

    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <div class="page">
      <!-- Sidebar -->
      <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
          <!-- Navbar Toggler -->
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <!-- Logo -->
          <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ url('/') }}" class="navbar-brand text-decoration-none">
              <div class="d-flex align-items-center">
                <svg class="icon me-2 text-primary" width="24" height="24" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                  <path fill="currentColor" d="M272.064 319.984H48c-17.68 0-32 14.32-32 32V992c0 17.68 14.32 32 32 32h224.064c17.68 0 32-14.32 32-32V351.984c0-17.68-14.32-32-32-32zm-32 640.016H80V383.984h160.064V960zm383.68-449.744h-224.08c-17.68 0-32 14.32-32 32V992c0 17.68 14.32 32 32 32h224.08c17.68 0 32-14.32 32-32V542.256c0-17.696-14.304-32-32-32zm-32 449.744h-160.08V574.256h160.08V960zM976 0H752.272c-17.68 0-32 14.32-32 32v960c0 17.68 14.32 32 32 32H976c17.68 0 32-14.32 32-32V32c0-17.68-14.32-32-32-32zm-32 960H784.272V64H944v896z"/>
                </svg>
                <span class="fw-bold text-white fs-2">Sistem</span>
                <span class="fw-normal text-muted ms-1 fs-2">Monitoring</span>
              </div>
            </a>
          </div>
          
          <!-- Mobile Menu -->
          <div class="navbar-nav flex-row d-lg-none">
            <!-- Mobile User Menu -->
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                @php
                  $mobileRole = Auth::user()->getRoleNames()->first() ?? 'user';
                  $mobileInitials = collect(explode(' ', Auth::user()->name ?? 'U'))
                    ->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->join('');
                  $mobileAvatarColor = match($mobileRole) {
                    'staff' => '#206bc4', 'store' => '#2fb344', 'produksi' => '#f76707', default => '#616876'
                  };
                @endphp
                <span class="avatar avatar-sm fw-bold text-white" style="background-color: {{ $mobileAvatarColor }}">{{ $mobileInitials }}</span>
                <div class="d-none d-xl-block ps-2">
                  <div>{{ Auth::user()->name ?? 'Guest' }}</div>
                  <div class="mt-1 small text-secondary">{{ ucfirst($mobileRole) }}</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="{{ route('admin.profile.show') }}" class="dropdown-item">
                  <i class="ti ti-user-circle me-2"></i>Profil Saya
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                  <i class="ti ti-logout me-2"></i>Logout
                </a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </div>
          </div>
          
          <!-- Sidebar Menu -->
          <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
              @include('layouts.partials.menu')
            </ul>

            <!-- User Profile Section -->
            @auth
            @php
              $sidebarRole = Auth::user()->getRoleNames()->first() ?? 'user';
              $sidebarRoleBadge = match($sidebarRole) {
                'staff' => 'bg-blue-lt', 'store' => 'bg-green-lt', 'produksi' => 'bg-orange-lt', default => 'bg-secondary-lt'
              };
              $sidebarRoleLabel = match($sidebarRole) {
                'staff' => 'Staff', 'store' => 'Store', 'produksi' => 'Produksi', default => ucfirst($sidebarRole)
              };
              $sidebarAvatarColor = match($sidebarRole) {
                'staff' => '#206bc4', 'store' => '#2fb344', 'produksi' => '#f76707', default => '#616876'
              };
              $sidebarInitials = collect(explode(' ', Auth::user()->name ?? 'U'))
                ->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->join('');
            @endphp
            <div class="mt-auto border-top border-dark-subtle">
              <a href="#" id="sidebar-user-toggle" class="nav-link d-flex align-items-center gap-2 px-3 py-3"
                 role="button" aria-label="User menu">
                <span class="avatar avatar-sm flex-shrink-0 fw-bold text-white"
                      style="background-color: {{ $sidebarAvatarColor }}">
                  {{ $sidebarInitials }}
                </span>
                <div class="flex-fill overflow-hidden">
                  <div class="text-white fw-medium lh-1 text-truncate" style="font-size: 0.875rem">
                    {{ Auth::user()->name }}
                  </div>
                  <span class="badge {{ $sidebarRoleBadge }} mt-1" style="font-size: 0.65rem">
                    {{ $sidebarRoleLabel }}
                  </span>
                </div>
                <i class="ti ti-dots-vertical text-muted flex-shrink-0"></i>
              </a>
            </div>

            {{-- Floating user menu — moved to <body> on load so it escapes the sidebar --}}
            <div id="sidebar-user-menu" class="card shadow"
                 style="display:none; position:fixed; z-index:1055; min-width:248px;">
              <div class="d-flex align-items-center gap-2 px-3 pt-3 pb-2">
                <span class="avatar avatar-sm flex-shrink-0 fw-bold text-white"
                      style="background-color: {{ $sidebarAvatarColor }}">
                  {{ $sidebarInitials }}
                </span>
                <div class="overflow-hidden">
                  <div class="fw-medium text-truncate">{{ Auth::user()->name }}</div>
                  <div class="small text-muted text-truncate">{{ Auth::user()->email }}</div>
                </div>
              </div>
              <div class="px-3 pb-2">
                <span class="badge {{ $sidebarRoleBadge }}">{{ $sidebarRoleLabel }}</span>
              </div>
              <hr class="my-1">
              <div class="px-1">
                <a href="{{ route('admin.profile.show') }}" class="dropdown-item rounded py-2">
                  <i class="ti ti-user-circle me-2"></i>Profil Saya
                </a>
              </div>
              <hr class="my-1">
              <div class="px-1 pb-1">
                <a href="#" class="dropdown-item rounded py-2 text-danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                  <i class="ti ti-logout me-2"></i>Logout
                </a>
              </div>
              <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </div>
            @endauth
          </div>
        </div>
      </aside>
      
      <div class="page-wrapper">
        <!-- Header -->
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <!-- Page pre-title -->
                <div class="page-pretitle">
                  @yield('page-pretitle', 'Overview')
                </div>
                <h2 class="page-title">
                  @yield('page-title', 'Dashboard')
                </h2>
              </div>
              
              <!-- Page actions -->
              <div class="col-auto ms-auto d-print-none">
                @yield('page-actions')
              </div>
            </div>
          </div>
        </div>
        
        <!-- Page body -->
        <div class="page-body">
          <div class="container-xl">
            @yield('content')
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal container for dynamic modals -->
    <div id="modal-container"></div>
    
    @stack('scripts')

    <script>
      // Sidebar user menu: a floating panel moved to <body> so it renders
      // OUTSIDE the sidebar (right on desktop, above the toggle on mobile).
      document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('sidebar-user-toggle');
        var menu = document.getElementById('sidebar-user-menu');
        if (!toggle || !menu) return;

        document.body.appendChild(menu); // escape the dark, clipped sidebar

        function isOpen() { return menu.style.display === 'block'; }
        function close() { menu.style.display = 'none'; }
        function open() {
          var r = toggle.getBoundingClientRect();
          var isDesktop = window.matchMedia('(min-width: 992px)').matches;
          menu.style.display = 'block';
          var mh = menu.offsetHeight;
          var mw = menu.offsetWidth;
          if (isDesktop) {
            // Float to the right of the sidebar, bottom-aligned to the toggle.
            menu.style.left = (r.right + 8) + 'px';
            menu.style.top = Math.max(8, r.bottom - mh) + 'px';
          } else {
            // Float just above the toggle.
            menu.style.left = Math.min(r.left, window.innerWidth - mw - 8) + 'px';
            menu.style.top = Math.max(8, r.top - mh - 8) + 'px';
          }
        }

        toggle.addEventListener('click', function (e) {
          e.preventDefault();
          isOpen() ? close() : open();
        });
        document.addEventListener('click', function (e) {
          if (isOpen() && !menu.contains(e.target) && !toggle.contains(e.target)) close();
        });
        window.addEventListener('resize', close);
        window.addEventListener('scroll', function () { if (isOpen()) close(); }, true);
      });

      // ── Reusable numeric input guard ──────────────────────────────
      // Blocks invalid characters (letters, e/E, +, and an out-of-range
      // sign) from every <input type="number"> via ONE delegated listener.
      // Covers typing, paste and drag-drop, on desktop & mobile alike.
      // Rules are derived from each field's own attributes:
      //   • decimals allowed when step is "any" or a non-integer (e.g. 0.001)
      //   • negatives allowed only when min is absent or below 0
      (function () {
        function allowedPattern(el) {
          var step = el.getAttribute('step');
          var min = el.getAttribute('min');
          var allowDecimal = step === 'any' || (step !== null && !Number.isInteger(parseFloat(step)));
          var allowNegative = min === null || parseFloat(min) < 0;
          return new RegExp('^[0-9' + (allowDecimal ? '.' : '') + (allowNegative ? '\\-' : '') + ']*$');
        }
        document.addEventListener('beforeinput', function (e) {
          var el = e.target;
          if (!(el instanceof HTMLInputElement) || el.type !== 'number') return;
          if (e.data == null) return; // deletions / non-text input methods
          if (!allowedPattern(el).test(e.data)) e.preventDefault();
        });
      })();

      // Toastr configuration
      toastr.options = {
          "closeButton": true,
          "debug": false,
          "newestOnTop": true,
          "progressBar": true,
          "positionClass": "toast-top-right",
          "preventDuplicates": false,
          "onclick": null,
          "showDuration": "300",
          "hideDuration": "1000",
          "timeOut": "5000",
          "extendedTimeOut": "1000",
          "showEasing": "swing",
          "hideEasing": "linear",
          "showMethod": "fadeIn",
          "hideMethod": "fadeOut"
      };
      
      // Show toastr notifications
      @if(session('success'))
          toastr.success('{{ session('success') }}');
      @endif
      
      @if(session('error'))
          toastr.error('{{ session('error') }}');
      @endif
      
      @if(session('info'))
          toastr.info('{{ session('info') }}');
      @endif
      
      @if(session('warning'))
          toastr.warning('{{ session('warning') }}');
      @endif
    </script>
</body>
</html>