{{-- Block 5 - Exam and Grades Management | Student name / number: ________ --}}
{{--
    Fields shared by the create and edit exam forms.
    Expects $types, $courses, $sections; optional $exam (edit) and $hasGrades (locks course/section).
--}}
@php
    $lock = $hasGrades ?? false;
    $val = fn (string $field, $default = null) => old($field, isset($exam) ? $exam->{$field} : $default);
@endphp

@if($lock)
    <div class="alert alert-warning">
        <i class="bi bi-lock me-1"></i> Grades are already recorded for this exam, so its course and section can no longer be changed.
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label for="exam_type_id" class="form-label fw-bold">Exam Type <span class="text-danger">*</span></label>
        <select name="exam_type_id" id="exam_type_id" class="form-select @error('exam_type_id') is-invalid @enderror" required>
            <option value="">-- Select type --</option>
            @foreach($types as $type)
                <option value="{{ $type->exam_type_id }}" {{ $val('exam_type_id') == $type->exam_type_id ? 'selected' : '' }}>
                    {{ $type->type_name }}
                </option>
            @endforeach
        </select>
        @error('exam_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="exam_date" class="form-label fw-bold">Exam Date <span class="text-danger">*</span></label>
        <input type="date" name="exam_date" id="exam_date" required
               class="form-control @error('exam_date') is-invalid @enderror"
               value="{{ old('exam_date', isset($exam) ? $exam->exam_date->format('Y-m-d') : '') }}">
        @error('exam_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="exam_time" class="form-label fw-bold">Exam Time <span class="text-danger">*</span></label>
        <input type="time" name="exam_time" id="exam_time" required
               class="form-control @error('exam_time') is-invalid @enderror"
               value="{{ old('exam_time', isset($exam) ? $exam->time_label : '') }}">
        @error('exam_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="duration" class="form-label fw-bold">Duration (minutes) <span class="text-danger">*</span></label>
        <input type="number" name="duration" id="duration" min="1" step="1" required
               class="form-control @error('duration') is-invalid @enderror" value="{{ $val('duration') }}">
        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="max_grade" class="form-label fw-bold">Maximum Grade (10 - 100) <span class="text-danger">*</span></label>
        <input type="number" name="max_grade" id="max_grade" min="10" max="100" step="0.01" required
               class="form-control @error('max_grade') is-invalid @enderror" value="{{ $val('max_grade') }}">
        @error('max_grade') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="min_grade" class="form-label fw-bold">Minimum (Passing) Grade <span class="text-danger">*</span></label>
        <input type="number" name="min_grade" id="min_grade" min="0.01" max="100" step="0.01" required
               class="form-control @error('min_grade') is-invalid @enderror" value="{{ $val('min_grade') }}">
        @error('min_grade') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="course_id" class="form-label fw-bold">Course <span class="text-danger">*</span></label>
        <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" {{ $lock ? 'disabled' : 'required' }}>
            <option value="">-- Select course --</option>
            @foreach($courses as $course)
                <option value="{{ $course->course_id }}" {{ $val('course_id') == $course->course_id ? 'selected' : '' }}>
                    {{ $course->course_code }} - {{ $course->course_name }}
                </option>
            @endforeach
        </select>
        @if($lock) <input type="hidden" name="course_id" value="{{ $exam->course_id }}"> @endif
        @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="section_id" class="form-label fw-bold">Section <span class="text-danger">*</span></label>
        <select name="section_id" id="section_id" class="form-select @error('section_id') is-invalid @enderror" {{ $lock ? 'disabled' : 'required' }}>
            <option value="">-- Select section --</option>
            @foreach($sections as $section)
                <option value="{{ $section->section_id }}" data-course="{{ $section->course_id }}"
                        {{ $val('section_id') == $section->section_id ? 'selected' : '' }}>
                    {{ $section->section_name }}
                </option>
            @endforeach
        </select>
        @if($lock) <input type="hidden" name="section_id" value="{{ $exam->section_id }}"> @endif
        @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="room" class="form-label fw-bold">Room <span class="text-danger">*</span></label>
        <input type="text" name="room" id="room" maxlength="50" required
               class="form-control @error('room') is-invalid @enderror" value="{{ $val('room') }}">
        @error('room') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@unless($lock)
<script>
    // Only offer the sections that belong to the chosen course.
    (function () {
        var course = document.getElementById('course_id');
        var section = document.getElementById('section_id');

        function filterSections() {
            Array.prototype.forEach.call(section.options, function (opt) {
                if (!opt.value) { return; }
                var hide = course.value && opt.getAttribute('data-course') !== course.value;
                opt.hidden = hide;
                opt.disabled = hide;
                if (hide && opt.selected) { section.value = ''; }
            });
        }

        course.addEventListener('change', filterSections);
        filterSections();
    })();

    // The passing grade must stay below the maximum grade (the server checks it again).
    (function () {
        var max = document.getElementById('max_grade');
        var min = document.getElementById('min_grade');

        function check() {
            var ok = !max.value || !min.value || parseFloat(min.value) < parseFloat(max.value);
            min.setCustomValidity(ok ? '' : 'The minimum grade must be less than the maximum grade.');
        }

        max.addEventListener('input', check);
        min.addEventListener('input', check);
        check();
    })();
</script>
@endunless
