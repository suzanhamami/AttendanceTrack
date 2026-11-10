{{-- Shown after login to accounts whose block has no home page in this branch yet. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - AttendanceTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container text-center" style="max-width: 480px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="fw-bold">Welcome, {{ auth()->user()->full_name }}</h5>
            <p class="text-muted mb-4">Your role (<strong>{{ auth()->user()->role->role_name ?? '-' }}</strong>) has no home page in this module yet.</p>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-primary">Logout</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
