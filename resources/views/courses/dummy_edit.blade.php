@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-secondary">
            <i class="bi bi-pencil-square me-1"></i> Edit Course (Phase 1 Dummy Simulation)
        </h5>
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

        <form action="{{ route('dummy.courses.update', $course['id']) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control" value="{{ old('course_name', $course['course_name']) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control" value="{{ old('course_code', $course['course_code']) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Credit Hours (1-6)</label>
                    <input type="number" name="credit_hours" class="form-control" min="1" max="6" value="{{ old('credit_hours', $course['credit_hours']) }}" required>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('dummy.courses.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Simulate Update Course</button>
            </div>
        </form>
    </div>
</div>
@endsection