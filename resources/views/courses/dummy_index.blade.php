@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-secondary">
            <i class="bi bi-card-checklist me-1"></i> Static Dummy Courses (Phase 1 Requirement)
        </h5>
        <small class="text-muted">Simulated array data rendered without database queries</small>
    </div>
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection