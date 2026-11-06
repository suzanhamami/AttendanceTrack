@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="bi bi-people-fill me-1"></i> Students Management
        </h5>
        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Student
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card-body bg-light border-bottom">
        <form method="GET" action="{{ route('students.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, university ID, or national ID..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="academic_year" class="form-select form-select-sm">
                    <option value="">-- All Academic Years --</option>
                    <option value="1" {{ request('academic_year') == '1' ? 'selected' : '' }}>Year 1</option>
                    <option value="2" {{ request('academic_year') == '2' ? 'selected' : '' }}>Year 2</option>
                    <option value="3" {{ request('academic_year') == '3' ? 'selected' : '' }}>Year 3</option>
                    <option value="4" {{ request('academic_year') == '4' ? 'selected' : '' }}>Year 4</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Students Grid View -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>University ID</th>
                        <th>Full Name</th>
                        <th>National ID</th>
                        <th>Phone / Email</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="fw-bold">{{ $student->university_id }}</td>
                            <td>{{ $student->full_name }}</td>
                            <td>{{ $student->national_id }}</td>
                            <td>
                                <small class="d-block"><i class="bi bi-telephone me-1"></i>{{ $student->phone }}</small>
                                <small class="d-block text-muted"><i class="bi bi-envelope me-1"></i>{{ $student->email }}</small>
                            </td>
                            <td>{{ $student->department->department_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary-subtle text-dark border">Year {{ $student->academic_year }}</span></td>
                            <td>
                                @if($student->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($student->status === 'suspended')
                                    <span class="badge bg-warning text-dark">Suspended</span>
                                @else
                                    <span class="badge bg-secondary">Graduated</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('students.show', $student->student_id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Manager exclusive actions --}}
                                @if(Auth::check() && (optional(Auth::user()->role)->role_name === 'manager' || Auth::user()->role_id == 1))
                                    <form action="{{ route('students.destroy', $student->student_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No students registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection