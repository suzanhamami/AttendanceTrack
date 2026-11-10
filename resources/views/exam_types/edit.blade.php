{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm col-md-6 mx-auto">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil me-1"></i> Rename Exam Type</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('exam_types.update', $type) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="type_name" class="form-label fw-bold">Type name <span class="text-danger">*</span></label>
            <input type="text" name="type_name" id="type_name" maxlength="50" required
                   class="form-control @error('type_name') is-invalid @enderror"
                   value="{{ old('type_name', $type->type_name) }}">
            @error('type_name') <div class="invalid-feedback">{{ $message }}</div> @enderror

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('exam_types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
