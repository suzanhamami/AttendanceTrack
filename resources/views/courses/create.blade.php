@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Register New Course</h5>
    </div>
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('courses.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control" value="{{ old('course_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control" placeholder="e.g. CS101" value="{{ old('course_code') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Credit Hours (1-6)</label>
                    <input type="number" name="credit_hours" class="form-control" min="1" max="6" value="{{ old('credit_hours', 3) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select" required>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->semester_id }}" {{ old('semester_id') == $sem->semester_id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Instructor</label>
                    <select name="instructor_id" class="form-select" required>
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->user_id }}" {{ old('instructor_id') == $inst->user_id ? 'selected' : '' }}>
                                {{ $inst->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Academic Year</label>
                    <input type="text" name="academic_year" class="form-control" value="{{ old('academic_year', '2026-2027') }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Prerequisite Course (Optional)</label>
                    <select name="prerequisite_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($prerequisites as $prereq)
                            <option value="{{ $prereq->course_id }}" {{ old('prerequisite_id') == $prereq->course_id ? 'selected' : '' }}>
                                {{ $prereq->course_code }} - {{ $prereq->course_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('courses.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Course</button>
            </div>
        </form>
    </div>
</div>
@endsection