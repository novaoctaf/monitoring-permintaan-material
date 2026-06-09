<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Authentication')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,500,600,700" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }
        .auth-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .btn-primary {
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 767.98px) {
            .auth-container {
                padding: 1rem;
            }
        }
        @media (min-width: 768px) {
            .auth-container {
                min-height: 100vh;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container auth-container d-flex justify-content-center align-items-center">
            <div class="col-12 col-md-10 col-lg-7 col-xl-5">
                <div class="card auth-card">
                    <div class="card-header py-4 text-center">
                        @yield('card-header')
                    </div>
                    <div class="card-body p-4">
                        @yield('content')
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted">
                    <small>&copy; {{ date('Y') }} PT. XYZ. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>