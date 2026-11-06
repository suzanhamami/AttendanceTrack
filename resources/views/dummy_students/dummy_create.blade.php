@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add New Student</h2>
    
    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <form action="{{ route('dummy.students.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">University ID</label>
                    <input type="text" name="university_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">National ID</label>
                    <input type="text" name="national_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Major</label>
                    <input type="text" name="major" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <input type="text" name="year" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Save Student</button>
                <a href="{{ route('dummy.students.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection