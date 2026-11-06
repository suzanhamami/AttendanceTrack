@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold text-secondary">
                <i class="bi bi-card-checklist me-1"></i> Static Dummy Courses 
            </h5>
        </div>
        <a href="{{ route('dummy.courses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Dummy Course
        </a>
    </div>

    <!-- Green Notification Message for Create / Edit / Delete -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Department</th>
                        <th>Instructor</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                        <tr>
                            <td>{{ $course['id'] }}</td>
                            <td class="fw-bold">{{ $course['course_code'] }}</td>
                            <td>{{ $course['course_name'] }}</td>
                            <td>{{ $course['credit_hours'] }} hrs</td>
                            <td>{{ $course['department'] }}</td>
                            <td>{{ $course['instructor'] }}</td>
                            <td>{{ $course['semester'] }}</td>
                            <td>
                                <span class="badge {{ $course['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($course['status']) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('dummy.courses.show', $course['id']) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('dummy.courses.edit', $course['id']) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('dummy.courses.destroy', $course['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Simulate deleting this dummy course?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection