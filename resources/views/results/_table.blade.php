{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{-- Student grid fragment (also returned alone to Ajax requests). Expects $students. --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>University ID</th>
                <th>Student</th>
                <th>Department</th>
                <th>Year</th>
                <th>Recorded grades</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td class="fw-bold">{{ $student->university_id }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->department->department_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark border">Year {{ $student->academic_year }}</span></td>
                    <td>
                        @if($student->grades_count > 0)
                            <span class="badge bg-success">{{ $student->grades_count }}</span>
                        @else
                            <span class="badge bg-secondary">0</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('results.show', $student->student_id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-1"></i> Issue statement
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-1"></i> No students found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($students->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }}</small>
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
@endif
