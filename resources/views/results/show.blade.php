{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="{{ route('results.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Print / Save as PDF
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3 text-center">
        <h4 class="mb-0 fw-bold">Final Grades Statement</h4>
        <small class="text-muted">Issued on {{ now()->format('Y-m-d') }}</small>
    </div>

    <div class="card-body">
        <!-- Student data -->
        <div class="row g-2 mb-4">
            <div class="col-sm-6"><strong>Student:</strong> {{ $student->full_name }}</div>
            <div class="col-sm-6"><strong>University ID:</strong> {{ $student->university_id }}</div>
            <div class="col-sm-6"><strong>Department:</strong> {{ $student->department->department_name ?? 'N/A' }}</div>
            <div class="col-sm-6"><strong>Academic year:</strong> Year {{ $student->academic_year }}</div>
        </div>

        @forelse($courses as $block)
            <h6 class="fw-bold text-primary mt-4">{{ $block['course']->course_code }} &mdash; {{ $block['course']->course_name }}</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Exam</th>
                            <th>Date</th>
                            <th>Grade</th>
                            <th>Percentage</th>
                            <th>Passing grade</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($block['grades'] as $grade)
                            @php
                                $passed = $grade->is_passed;
                                $percent = $grade->percentage;
                            @endphp
                            <tr>
                                <td>{{ $grade->exam->type->type_name }}</td>
                                <td>{{ $grade->exam->exam_date->format('Y-m-d') }}</td>
                                <td>{{ $grade->grade }} / {{ $grade->exam->max_grade }}</td>
                                <td>{{ $percent }}%</td>
                                <td>{{ $grade->exam->min_grade }}</td>
                                <td>
                                    <span class="badge {{ $passed ? 'bg-success' : 'bg-danger' }}">{{ $passed ? 'Passed' : 'Failed' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2">Course total</td>
                            <td>{{ $block['summary']['total'] }} / {{ $block['summary']['max_total'] }}</td>
                            <td colspan="3">{{ $block['summary']['percentage'] }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <div class="alert alert-info">This student has no recorded grades yet.</div>
        @endforelse

        @if($overall['exams'] > 0)
            <div class="alert alert-secondary mt-4 mb-0 d-flex justify-content-between flex-wrap">
                <span><strong>Exams graded:</strong> {{ $overall['exams'] }}</span>
                <span><strong>Passed:</strong> {{ $overall['passed_exams'] }}</span>
                <span><strong>Failed:</strong> {{ $overall['failed_exams'] }}</span>
                <span><strong>Overall:</strong> {{ $overall['total'] }} / {{ $overall['max_total'] }} ({{ $overall['percentage'] }}%)</span>
            </div>
        @endif
    </div>
</div>
@endsection
