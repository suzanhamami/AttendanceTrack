{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
@extends('layouts.app')

@section('content')
@include('exams._nav')

<div class="row g-4">
    <!-- Add a new exam type -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle me-1"></i> Add Exam Type</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('exam_types.store') }}" method="POST">
                    @csrf
                    <label for="type_name" class="form-label fw-bold">Type name <span class="text-danger">*</span></label>
                    <input type="text" name="type_name" id="type_name" maxlength="50" required
                           class="form-control @error('type_name') is-invalid @enderror" value="{{ old('type_name') }}"
                           placeholder="e.g. Quiz">
                    @error('type_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-save me-1"></i> Add</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Existing types -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-tags me-1"></i> Exam Types</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Exams using it</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td class="fw-bold">{{ $type->type_name }}</td>
                                <td><span class="badge bg-secondary">{{ $type->exams_count }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('exam_types.edit', $type) }}" class="btn btn-sm btn-outline-primary" title="Rename">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('exam_types.destroy', $type) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this exam type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                {{ $type->exams_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No exam types yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
