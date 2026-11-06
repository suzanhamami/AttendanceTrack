@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Student</h2>
    
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <form action="{{ route('dummy.students.update', $student['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" value="{{ $student['full_name'] }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">University ID</label>
                    <input type="text" name="university_id" value="{{ $student['university_id'] }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" value="{{ $student['national_id'] }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Major</label>
                    <input type="text" name="major" value="{{ $student['major'] }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <input type="text" name="year" value="{{ $student['year'] }}" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Student</button>
                <a href="{{ route('dummy.students.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection