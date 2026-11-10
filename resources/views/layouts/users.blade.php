{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Users Management') - AttendanceTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .avatar-lg { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .avatar-initials { display: inline-flex; align-items: center; justify-content: center; background: #e7f1ff; color: #0d6efd; font-weight: 600; }
        .stat-card .bi { font-size: 2rem; }
        .dash-btn { min-height: 110px; }
        #users-grid.loading { opacity: .5; pointer-events: none; }
        @media (max-width: 575.98px) {
            .table-responsive-stack td:nth-child(n+4):not(:last-child),
            .table-responsive-stack th:nth-child(n+4):not(:last-child) { display: none; }
            h4, h5 { font-size: 1.05rem; }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('users.dashboard') }}">
            <i class="bi bi-mortarboard-fill me-1"></i> AttendanceTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            @auth
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('users.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') && !request()->routeIs('users.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people me-1"></i> Users
                    </a>
                </li>
                @if(\App\Support\Block1\Access::has(auth()->user(), 'roles.manage'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('roles.*') ? 'active fw-bold' : '' }}" href="{{ route('roles.index') }}">
                        <i class="bi bi-shield-lock me-1"></i> Roles &amp; Permissions
                    </a>
                </li>
                @endif
            </ul>

            <div class="d-flex align-items-center text-white flex-wrap gap-2 py-2 py-lg-0">
                <span class="badge bg-light text-primary text-uppercase">{{ auth()->user()->role->role_name ?? 'User' }}</span>
                <span>{{ auth()->user()->full_name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>

<main class="container mb-5">
    <div id="flash">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    @yield('content')
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Every jQuery Ajax request sends the CSRF token.
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Shows a dismissible alert at the top of the page (used by the Ajax actions).
    function showFlash(message, ok) {
        var $alert = $('<div class="alert alert-dismissible fade show" role="alert"><span></span>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        $alert.addClass(ok ? 'alert-success' : 'alert-danger').find('span').text(message);   // .text() -> no XSS
        $('#flash').html($alert);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
@stack('scripts')
</body>
</html>
