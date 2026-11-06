@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Students Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('dummy.students.create') }}" class="btn btn-primary">Add New Student</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>University ID</th>
                        <th>Major</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student['id'] }}</td>
                            <td>{{ $student['full_name'] }}</td>
                            <td>{{ $student['university_id'] }}</td>
                            <td>{{ $student['major'] }}</td>
                            <td>{{ $student['year'] }}</td>
                            <td>
                                <a href="{{ route('dummy.students.show', $student['id']) }}" class="btn btn-sm btn-info text-white">View</a>
                                <a href="{{ route('dummy.students.edit', $student['id']) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('dummy.students.destroy', $student['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection