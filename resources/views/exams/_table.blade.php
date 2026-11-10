{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{-- Grid View fragment (also returned alone to Ajax requests). Expects $exams and $isManager. --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Type</th>
                <th>Course / Section</th>
                <th>Date &amp; Time</th>
                <th>Duration</th>
                <th>Room</th>
                <th>Max / Min</th>
                <th>Graded</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exams as $exam)
                <tr>
                    <td><span class="badge bg-primary-subtle text-primary border">{{ $exam->type->type_name ?? 'N/A' }}</span></td>
                    <td>
                        <span class="fw-bold">{{ $exam->course->course_code ?? '' }}</span>
                        <small class="d-block text-muted">{{ $exam->course->course_name ?? 'N/A' }} &middot; {{ $exam->section->section_name ?? 'N/A' }}</small>
                    </td>
                    <td>
                        {{ $exam->exam_date->format('Y-m-d') }}
                        <small class="d-block text-muted"><i class="bi bi-clock me-1"></i>{{ $exam->time_label }}</small>
                    </td>
                    <td>{{ $exam->duration }} min</td>
                    <td>{{ $exam->room }}</td>
                    <td>{{ $exam->max_grade }} / {{ $exam->min_grade }}</td>
                    <td>
                        @if($exam->grades_count > 0)
                            <span class="badge bg-success">{{ $exam->grades_count }} grade(s)</span>
                        @else
                            <span class="badge bg-secondary">None</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-outline-info" title="View details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('exams.grades.edit', $exam) }}" class="btn btn-sm btn-outline-success" title="Record grades">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="{{ route('exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary" title="Edit exam">
                            <i class="bi bi-pencil"></i>
                        </a>

                        {{-- Manager exclusive action --}}
                        @if($isManager)
                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this exam? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete exam">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-1"></i> No exams found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($exams->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $exams->firstItem() }}-{{ $exams->lastItem() }} of {{ $exams->total() }}</small>
        {{ $exams->links('pagination::bootstrap-5') }}
    </div>
@endif
