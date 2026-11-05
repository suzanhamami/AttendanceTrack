<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AttendanceTrack - Course Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <!-- !!!!!!!!!!!!!!!!!!!!!!!!!!!re write the link for courses here -->
        <a class="navbar-brand fw-bold" href="{{ route('dummy.students.index') }}">
            <i class="bi bi-mortarboard-fill me-1"></i> AttendanceTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            @auth
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('courses.*') ? 'active fw-bold' : '' }}" href="{{ route('courses.index') }}">
                        <i class="bi bi-database me-1"></i> Courses (DB)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dummy.courses.*') ? 'active fw-bold' : '' }}" href="{{ route('dummy.courses.index') }}">
                        <i class="bi bi-card-checklist me-1"></i> Dummy Data (Phase 1)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('students.*') ? 'active fw-bold' : '' }}" href="{{ route('students.index') }}">
                        <i class="bi bi-people-fill me-1"></i> Students (DB)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dummy.students.*') ? 'active fw-bold' : '' }}" href="{{ route('dummy.students.index') }}">
                        <i class="bi bi-person-lines-fill me-1"></i> Dummy Students (Phase 1)
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center text-white">
                <span class="badge bg-light text-primary me-2 text-uppercase">
                    {{ Auth::user()->role->role_name ?? 'User' }}
                </span>
                <span class="me-3">{{ Auth::user()->full_name }}</span>
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

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

