{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{-- Block 5 home page: statistics + control-panel buttons that depend on the user's role. --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="mb-3">
    <h4 class="fw-bold text-primary mb-0"><i class="bi bi-speedometer2 me-1"></i> Exams &amp; Grades Dashboard</h4>
    <small class="text-muted">
        Welcome, {{ Auth::user()->full_name }} - {{ $isManager ? 'Department Manager' : 'Department Employee' }}
    </small>
</div>

<!-- Statistics -->
@php
    $cards = [
        ['label' => 'Exams',           'value' => $stats['exams'],    'icon' => 'bi-journal-check',   'color' => 'primary'],
        ['label' => 'Upcoming exams',  'value' => $stats['upcoming'], 'icon' => 'bi-calendar-event',  'color' => 'info'],
        ['label' => 'Recorded grades', 'value' => $stats['grades'],   'icon' => 'bi-clipboard-data',  'color' => 'success'],
        ['label' => 'Awaiting grades', 'value' => $stats['awaiting'], 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($cards as $card)
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-{{ $card['color'] }}">
                <div class="card-body d-flex align-items-center">
                    <i class="bi {{ $card['icon'] }} fs-2 text-{{ $card['color'] }} me-3"></i>
                    <div>
                        <div class="fs-4 fw-bold">{{ $card['value'] }}</div>
                        <div class="text-muted small">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Control panel: buttons according to the role -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-grid-3x3-gap me-1"></i> Control Panel</div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('exams.create') }}" class="btn btn-primary w-100 py-3">
                    <i class="bi bi-plus-circle d-block fs-4"></i> Add Exam
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('exams.index') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-search d-block fs-4"></i> Search / Edit Exams
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('exams.index') }}" class="btn btn-outline-success w-100 py-3">
                    <i class="bi bi-pencil-square d-block fs-4"></i> Record Grades
                </a>
            </div>
            @if($isManager)
                <div class="col-6 col-md-4 col-xl">
                    <a href="{{ route('exam_types.index') }}" class="btn btn-outline-secondary w-100 py-3">
                        <i class="bi bi-tags d-block fs-4"></i> Manage Exam Types
                    </a>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <a href="{{ route('results.index') }}" class="btn btn-outline-dark w-100 py-3">
                        <i class="bi bi-file-earmark-text d-block fs-4"></i> Issue Results
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Next exams -->
<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold"><i class="bi bi-calendar-week me-1"></i> Upcoming Exams</div>
    <div class="card-body p-0">
        @if($upcoming->isEmpty())
            <p class="text-muted text-center my-4">No upcoming exams.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcoming as $exam)
                            <tr>
                                <td>{{ $exam->exam_date->format('Y-m-d') }}</td>
                                <td>{{ $exam->time_label }}</td>
                                <td><span class="badge bg-secondary">{{ $exam->type->type_name ?? '-' }}</span></td>
                                <td>{{ $exam->course->course_code ?? '' }} - {{ $exam->course->course_name ?? '' }}</td>
                                <td>{{ $exam->section->section_name ?? '-' }}</td>
                                <td>{{ $exam->room }}</td>
                                <td class="text-end">
                                    <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-outline-primary" title="Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
