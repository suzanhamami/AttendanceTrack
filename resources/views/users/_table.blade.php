{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{-- Users Grid View. Returned alone for Ajax search requests. Action buttons depend on permissions. --}}
@php($me = auth()->user())
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 table-responsive-stack">
        <thead class="table-light">
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Department</th>
                <th>Phone</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @include('users._avatar', ['user' => $user])
                        <div>
                            <div class="fw-semibold">{{ $user->full_name }}</div>
                            <small class="text-muted">{{ '@' . $user->username }} &middot; {{ $user->email }}</small>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-primary-subtle text-primary text-capitalize">{{ $user->role->role_name ?? '-' }}</span></td>
                <td>@include('users._status_badge', ['status' => $user->status])</td>
                <td>{{ \App\Support\Block1\Access::departmentName($user) }}</td>
                <td>{{ $user->phone ?? '-' }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details"><i class="bi bi-eye"></i></a>
                    @if(\App\Support\Block1\Access::has($me, 'users.edit') && (!\App\Support\Block1\Access::isPrivileged($user) || \App\Support\Block1\Access::isPrivileged($me)))
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                    @endif
                    @if(\App\Support\Block1\Access::has($me, 'users.status') && !$user->is($me))
                        <button type="button" class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} js-toggle-status"
                                data-url="{{ route('users.toggle-status', $user) }}"
                                title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                            <i class="bi {{ $user->status === 'active' ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                        </button>
                    @endif
                    @if(\App\Support\Block1\Access::has($me, 'users.delete') && !$user->is($me))
                        <button type="button" class="btn btn-sm btn-outline-danger js-delete"
                                data-url="{{ route('users.destroy', $user) }}" data-name="{{ $user->full_name }}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block"></i>No users match the selected filters.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}</small>
        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="p-3 border-top"><small class="text-muted">{{ $users->total() }} user(s)</small></div>
@endif
