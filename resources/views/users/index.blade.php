{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
@extends('layouts.users')
@section('title', 'Users')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-1"></i> Users</h5>
        @if(\App\Support\Block1\Access::has(auth()->user(), 'users.create'))
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add User</a>
        @endif
    </div>

    {{-- Advanced search: text + role + status + department. Results update with Ajax (no page reload). --}}
    <div class="card-body bg-light border-bottom">
        <form id="user-filter-form" method="GET" action="{{ route('users.index') }}" class="row g-2">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" maxlength="100" class="form-control"
                           placeholder="Name, username, email or phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="role_id" class="form-select form-select-sm" aria-label="Role">
                    <option value="">All roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->role_id }}" @selected(request('role_id') == $role->role_id)>{{ ucfirst($role->role_name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" aria-label="Status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-9 col-md-3">
                <select name="department_id" class="form-select form-select-sm" aria-label="Department">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->department_id }}" @selected(request('department_id') == $department->department_id)>{{ $department->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3 col-md-1">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Reset filters"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>

    {{-- Grid View --}}
    <div class="card-body p-0" id="users-grid">
        @include('users._table')
    </div>
</div>
@endsection

@push('scripts')
<script>
(function ($) {
    var $form = $('#user-filter-form');
    var $grid = $('#users-grid');
    var timer = null;

    // Loads the grid fragment from the server with Ajax.
    function load(url) {
        $grid.addClass('loading');
        $.ajax({ url: url, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .done(function (html) {
                $grid.html(html);
                window.history.replaceState(null, '', url);   // keep the filters in the address bar
            })
            .fail(function () { $grid.html('<div class="alert alert-danger m-3">Could not load the results. Please try again.</div>'); })
            .always(function () { $grid.removeClass('loading'); });
    }

    function search() { load($form.attr('action') + '?' + $form.serialize()); }

    // Search while typing (debounced) and whenever a filter changes.
    $form.on('input change', 'input, select', function () {
        clearTimeout(timer);
        timer = setTimeout(search, 300);
    });
    $form.on('submit', function (e) { e.preventDefault(); search(); });

    // Pagination links through Ajax too.
    $grid.on('click', '.pagination a', function (e) { e.preventDefault(); load($(this).attr('href')); });

    // Manager: activate / deactivate without reloading.
    $grid.on('click', '.js-toggle-status', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.ajax({ url: $btn.data('url'), method: 'PATCH', dataType: 'json' })
            .done(function (res) { showFlash(res.message, true); load(window.location.href); })
            .fail(function (xhr) { showFlash((xhr.responseJSON && xhr.responseJSON.message) || 'Action failed.', false); })
            .always(function () { $btn.prop('disabled', false); });
    });

    // Manager: delete after confirmation.
    $grid.on('click', '.js-delete', function () {
        var $btn = $(this);
        if (!confirm('Delete "' + $btn.data('name') + '"? This cannot be undone.')) { return; }
        $btn.prop('disabled', true);
        $.ajax({ url: $btn.data('url'), method: 'DELETE', dataType: 'json' })
            .done(function (res) { showFlash(res.message, true); load(window.location.href); })
            .fail(function (xhr) { showFlash((xhr.responseJSON && xhr.responseJSON.message) || 'Action failed.', false); })
            .always(function () { $btn.prop('disabled', false); });
    });
})(jQuery);
</script>
@endpush
