{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="bi bi-journal-check me-1"></i> Exams &amp; Grades Management
        </h5>
        <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Exam
        </a>
    </div>

    <!-- Advanced search: type, section, date, course (results update with Ajax) -->
    <div class="card-body bg-light border-bottom">
        <form id="exam-filter-form" method="GET" action="{{ route('exams.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search course, section or room..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="exam_type_id" class="form-select form-select-sm">
                    <option value="">-- All Types --</option>
                    @foreach($types as $type)
                        <option value="{{ $type->exam_type_id }}" {{ request('exam_type_id') == $type->exam_type_id ? 'selected' : '' }}>
                            {{ $type->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">-- All Courses --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->course_id }}" {{ request('course_id') == $course->course_id ? 'selected' : '' }}>
                            {{ $course->course_code }} - {{ $course->course_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="section_id" class="form-select form-select-sm">
                    <option value="">-- All Sections --</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->section_id }}" {{ request('section_id') == $section->section_id ? 'selected' : '' }}>
                            {{ $section->section_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="exam_date" class="form-control form-control-sm" value="{{ request('exam_date') }}">
            </div>
            <div class="col-md-1">
                <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Reset filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Exams Grid View -->
    <div class="card-body p-0" id="exams-grid">
        @include('exams._table')
    </div>
</div>

@include('exams._ajax_grid', ['form' => '#exam-filter-form', 'grid' => '#exams-grid'])
@endsection
