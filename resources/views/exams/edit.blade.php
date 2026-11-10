{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="card shadow-sm col-lg-10 mx-auto">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil me-1"></i> Edit Exam #{{ $exam->exam_id }}</h5>
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

        <form action="{{ route('exams.update', $exam) }}" method="POST">
            @csrf
            @method('PUT')
            @include('exams._form')

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Exam</button>
            </div>
        </form>
    </div>
</div>
@endsection
