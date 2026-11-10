{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-file-earmark-text me-1"></i> Issue Results</h5>
        <small class="text-muted">Find a student and issue the final grades statement.</small>
    </div>

    <div class="card-body bg-light border-bottom">
        <form id="result-filter-form" method="GET" action="{{ route('results.index') }}" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search by name or university ID..." value="{{ request('search') }}">
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
                    @foreach(['1', '2', '3', '4'] as $year)
                        <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>Year {{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <a href="{{ route('results.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Reset filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0" id="results-grid">
        @include('results._table')
    </div>
</div>

@include('exams._ajax_grid', ['form' => '#result-filter-form', 'grid' => '#results-grid'])
@endsection
