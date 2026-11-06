@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">{{ $student->full_name }} ({{ $student->university_id }})</h5>
        <span class="badge {{ $student->status === 'active' ? 'bg-success' : ($student->status === 'suspended' ? 'bg-warning text-dark' : 'bg-secondary') }}">
            {{ ucfirst($student->status) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <strong>National ID:</strong> {{ $student->national_id }}
            </div>
            <div class="col-sm-6">
                <strong>Department:</strong> {{ $student->department->department_name ?? 'N/A' }}
            </div>
            <div class="col-sm-6">
                <strong>Email:</strong> {{ $student->email }}
            </div>
            <div class="col-sm-6">
                <strong>Phone:</strong> {{ $student->phone }}
            </div>
            <div class="col-sm-6">
                <strong>Birth Date:</strong> {{ $student->birth_date }}
            </div>
            <div class="col-sm-6">
                <strong>Academic Year:</strong> Year {{ $student->academic_year }}
            </div>

            <!-- Registered Sections List -->
            <div class="col-12 mt-4">
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-journal-bookmark me-1"></i> Enrolled Sections</h6>
                @if($student->sections->count() > 0)
                    <ul class="list-group">
                        @foreach($student->sections as $section)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">{{ $section->section_name }}</span>
                                    <small class="text-muted d-block">Course: {{ $section->course->course_name ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-secondary">Registered: {{ $section->pivot->registration_date ?? 'N/A' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small fst-italic">No sections registered for this student yet.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer bg-white text-end">
        <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">Back to Students</a>
        <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-primary btn-sm">Edit Student</a>
    </div>
</div>
@endsection