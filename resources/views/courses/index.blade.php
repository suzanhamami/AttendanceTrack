@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="bi bi-book-half me-1"></i> Courses Management
        </h5>
        <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Course
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card-body bg-light border-bottom">
        <form method="GET" action="{{ route('courses.index') }}" class="row g-2">
            <div class="col-md-4">
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
                <select name="semester_id" class="form-select form-select-sm">
                    <option value="">-- All Semesters --</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->semester_id }}" {{ request('semester_id') == $sem->semester_id ? 'selected' : '' }}>
                            {{ $sem->semester_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="academic_year" class="form-control form-control-sm" placeholder="Academic Year (e.g. 2026-2027)" value="{{ request('academic_year') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Courses Grid View -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Prerequisite</th>
                        <th>Credit Hours</th>
                        <th>Department</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td class="fw-bold">{{ $course->course_code }}</td>
                            <td>{{ $course->course_name }}</td>
                            
                            <!-- Prerequisite Visual Badge -->
                            <td>
                                @if($course->prerequisite)
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                        <i class="bi bi-arrow-return-right me-1"></i>{{ $course->prerequisite->course_code }}
                                    </span>
                                    <small class="d-block text-muted">{{ $course->prerequisite->course_name }}</small>
                                @else
                                    <span class="text-muted small fst-italic">None</span>
                                @endif
                            </td>

                            <td>{{ $course->credit_hours }} hrs</td>
                            <td>{{ $course->department->department_name ?? 'N/A' }}</td>
                            <td>{{ $course->instructor->full_name ?? 'N/A' }}</td>
                            <td>
                                @if($course->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('courses.show', $course->course_id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('courses.edit', $course->course_id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Manager exclusive actions: status toggle & delete --}}
                                @if(Auth::check() && (optional(Auth::user()->role)->role_name === 'manager' || Auth::user()->role_id == 1))
                                    <form action="{{ route('courses.toggle-status', $course->course_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                            <i class="bi bi-toggle2-on"></i> Toggle
                                        </button>
                                    </form>

                                    <form action="{{ route('courses.destroy', $course->course_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No courses registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $courses->withQueryString()->links() }}
    </div>
</div>
@endsection