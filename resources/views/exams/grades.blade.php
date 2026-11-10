{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-1 fw-bold text-primary"><i class="bi bi-pencil-square me-1"></i> Record Grades</h5>
        <div class="text-muted">
            {{ $exam->type->type_name }} &middot; {{ $exam->course->course_code }} {{ $exam->course->course_name }}
            &middot; {{ $exam->section->section_name }} &middot; {{ $exam->exam_date->format('Y-m-d') }}
            &middot; Maximum <strong>{{ $exam->max_grade }}</strong> / Passing <strong>{{ $exam->min_grade }}</strong>
        </div>
    </div>

    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($students->isEmpty())
            <div class="alert alert-warning mb-0">No students are registered in this section, so there is nobody to grade.</div>
        @else
            <form action="{{ route('exams.grades.update', $exam) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="recorded_date" class="form-label fw-bold">Recording date <span class="text-danger">*</span></label>
                        <input type="date" name="recorded_date" id="recorded_date" required max="{{ now()->format('Y-m-d') }}"
                               class="form-control @error('recorded_date') is-invalid @enderror"
                               value="{{ old('recorded_date', now()->format('Y-m-d')) }}">
                        @error('recorded_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-9 d-flex align-items-end text-muted small">
                        Leave a field empty for a student who has no grade yet. Filling a field that already has a grade updates it.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>University ID</th>
                                <th>Student</th>
                                <th style="width: 200px;">Grade (0 - {{ $exam->max_grade }})</th>
                                <th>Percentage</th>
                                <th>Current record</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $existing = $grades->get($student->student_id);
                                    $key = $student->student_id;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $student->university_id }}</td>
                                    <td>{{ $student->full_name }}</td>
                                    <td>
                                        <input type="number" name="grades[{{ $key }}]" step="0.01" min="0" max="{{ $exam->max_grade }}"
                                               class="form-control form-control-sm js-grade @error('grades.' . $key) is-invalid @enderror"
                                               value="{{ old('grades.' . $key, $existing?->grade) }}">
                                        @error('grades.' . $key) <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                    <td class="js-percent text-muted">-</td>
                                    <td>
                                        @if($existing)
                                            <span class="badge bg-info text-dark">{{ $existing->grade }}</span>
                                            <small class="text-muted">on {{ $existing->recorded_date->format('Y-m-d') }}</small>
                                        @else
                                            <span class="text-muted">Not graded</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Save Grades</button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
    // Live percentage / pass-fail preview next to each grade input (the server validates again).
    (function () {
        var max = {{ (float) $exam->max_grade }};
        var min = {{ (float) $exam->min_grade }};

        document.querySelectorAll('.js-grade').forEach(function (input) {
            var cell = input.closest('tr').querySelector('.js-percent');

            function refresh() {
                if (input.value === '') { cell.textContent = '-'; return; }
                var value = parseFloat(input.value);
                if (isNaN(value) || value < 0 || value > max) { cell.textContent = 'Invalid'; return; }
                cell.textContent = (value / max * 100).toFixed(2) + '% ' + (value >= min ? '(Pass)' : '(Fail)');
            }

            input.addEventListener('input', refresh);
            refresh();
        });
    })();
</script>
@endsection
