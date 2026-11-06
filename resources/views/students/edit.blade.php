@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Edit Student: {{ $student->full_name }}</h5>
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

        <form action="{{ route('students.update', $student->student_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $student->full_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">University ID</label>
                    <input type="text" name="university_id" class="form-control" value="{{ old('university_id', $student->university_id) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $student->birth_date) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}" {{ old('department_id', $student->department_id) == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Academic Year</label>
                    <select name="academic_year" class="form-select" required>
                        <option value="1" {{ old('academic_year', $student->academic_year) == '1' ? 'selected' : '' }}>Year 1</option>
                        <option value="2" {{ old('academic_year', $student->academic_year) == '2' ? 'selected' : '' }}>Year 2</option>
                        <option value="3" {{ old('academic_year', $student->academic_year) == '3' ? 'selected' : '' }}>Year 3</option>
                        <option value="4" {{ old('academic_year', $student->academic_year) == '4' ? 'selected' : '' }}>Year 4</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $student->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Student</button>
            </div>
        </form>
    </div>
</div>
@endsection