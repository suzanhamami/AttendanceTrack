{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{-- Profile photo, or the user's initials when there is no photo. Expects $user and optional $class. --}}
@php($class = $class ?? 'avatar')
@if(\App\Support\Block1\Access::photoUrl($user))
    <img src="{{ \App\Support\Block1\Access::photoUrl($user) }}" alt="{{ $user->full_name }}" class="{{ $class }}">
@else
    <span class="{{ $class }} avatar-initials">{{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}</span>
@endif
