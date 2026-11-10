{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
@if($status === 'active')
    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Active</span>
@else
    <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-slash-circle me-1"></i>Inactive</span>
@endif
