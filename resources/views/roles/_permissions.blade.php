{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{-- Permission checkboxes grouped by block. Expects $groups, $checked (array of names) and $prefix (unique id prefix). --}}
@foreach($groups as $groupName => $permissions)
    <div class="mb-2">
        <div class="small fw-semibold text-muted mb-1">{{ $groupName }}</div>
        <div class="row g-1">
            @foreach($permissions as $name => $label)
                <div class="col-sm-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $name }}"
                               id="{{ $prefix }}-{{ $name }}" @checked(in_array($name, $checked, true))>
                        <label class="form-check-label small" for="{{ $prefix }}-{{ $name }}">
                            {{ $label }} <code class="text-muted">{{ $name }}</code>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
