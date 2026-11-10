{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{-- Block 5 sub-navigation. Buttons are shown according to the user's role (manager / employee). --}}
<ul class="nav nav-pills mb-3 no-print">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('exams.dashboard') ? 'active' : '' }}" href="{{ route('exams.dashboard') }}">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('exams.*') && ! request()->routeIs('exams.dashboard') ? 'active' : '' }}" href="{{ route('exams.index') }}">
            <i class="bi bi-journal-check me-1"></i> Exams
        </a>
    </li>
    @if(!empty($isManager))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('exam_types.*') ? 'active' : '' }}" href="{{ route('exam_types.index') }}">
                <i class="bi bi-tags me-1"></i> Exam Types
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('results.*') ? 'active' : '' }}" href="{{ route('results.index') }}">
                <i class="bi bi-file-earmark-text me-1"></i> Results
            </a>
        </li>
    @endif
</ul>

<style>
    @media print {
        .navbar, .no-print { display: none !important; }
        body { background: #fff !important; }
    }
</style>
