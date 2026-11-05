@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Student Details</h2>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $student['id'] }}</p>
            <p><strong>Full Name:</strong> {{ $student['full_name'] }}</p>
            <p><strong>University ID:</strong> {{ $student['university_id'] }}</p>
            <p><strong>National ID:</strong> {{ $student['national_id'] }}</p>
            <p><strong>Major:</strong> {{ $student['major'] }}</p>
            <p><strong>Academic Year:</strong> {{ $student['year'] }}</p>

            <a href="{{ route('dummy.students.index') }}" class="btn btn-secondary mt-3">Back to List</a>
        </div>
    </div>
</div>
@endsection