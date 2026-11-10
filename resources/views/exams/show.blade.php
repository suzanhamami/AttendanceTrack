{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">
            {{ $exam->type->type_name }} &mdash; {{ $exam->course->course_code }} {{ $exam->course->course_name }}
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('exams.grades.edit', $exam) }}" class="btn btn-success btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Record Grades
            </a>
            <a href="{{ route('exams.edit', $exam) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            {{-- Manager exclusive action --}}
            @if($isManager)
                <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this exam? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Delete</button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3"><strong>Date:</strong> {{ $exam->exam_date->format('Y-m-d') }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Time:</strong> {{ $exam->time_label }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Duration:</strong> {{ $exam->duration }} minutes</div>
            <div class="col-sm-6 col-lg-3"><strong>Room:</strong> {{ $exam->room }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Section:</strong> {{ $exam->section->section_name }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Instructor:</strong> {{ $exam->section->instructor->full_name ?? 'N/A' }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Maximum grade:</strong> {{ $exam->max_grade }}</div>
            <div class="col-sm-6 col-lg-3"><strong>Passing grade:</strong> {{ $exam->min_grade }}</div>
        </div>
    </div>
</div>

<!-- Statistics of the recorded grades -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center"><div class="card-body py-3">
            <div class="text-muted small">Graded students</div>
            <div class="fs-4 fw-bold">{{ $stats['count'] }} / {{ $students->count() }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center"><div class="card-body py-3">
            <div class="text-muted small">Average</div>
            <div class="fs-4 fw-bold">{{ $stats['average'] ?? '-' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center"><div class="card-body py-3">
            <div class="text-muted small">Highest / Lowest</div>
            <div class="fs-4 fw-bold">{{ $stats['highest'] ?? '-' }} / {{ $stats['lowest'] ?? '-' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center"><div class="card-body py-3">
            <div class="text-muted small">Pass rate</div>
            <div class="fs-4 fw-bold">{{ $stats['pass_rate'] !== null ? $stats['pass_rate'] . '%' : '-' }}</div>
        </div></div>
    </div>
</div>

<!-- Students of the section with their grades -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-1"></i> Students &amp; Grades</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>University ID</th>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Percentage</th>
                    <th>Result</th>
                    <th>Recorded on</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php $grade = $grades->get($student->student_id); @endphp
                    <tr>
                        <td class="fw-bold">{{ $student->university_id }}</td>
                        <td>{{ $student->full_name }}</td>
                        @if($grade)
                            @php
                                $passed = $grade->is_passed;
                                $percent = $grade->percentage;
                            @endphp
                            <td>{{ $grade->grade }} / {{ $exam->max_grade }}</td>
                            <td>{{ $percent }}%</td>
                            <td>
                                @if($passed)
                                    <span class="badge bg-success">Passed</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>{{ $grade->recorded_date->format('Y-m-d') }}</td>
                        @else
                            <td colspan="4" class="text-muted">Not graded yet</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No students are registered in this section.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to exams</a>
</div>
@endsection
