<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Role helpers for the Block 5 controllers.
 *
 * Routes are already protected by the `role:` middleware (CheckRole). The spec also asks for
 * permission checks at Controller level, not only in the views, so destructive / manager-only
 * actions call abortUnlessManager() as a second line of defence.
 * The role is resolved exactly like CheckRole does: from the `role` relationship and its role_name
 * (never from a hard-coded role id).
 */
trait ResolvesUserRole
{
    protected function currentRole(): ?string
    {
        $user = Auth::user();

        return $user?->role ? strtolower($user->role->role_name) : null;
    }

    protected function isManager(): bool
    {
        return $this->currentRole() === 'manager';
    }

    protected function abortUnlessManager(): void
    {
        abort_unless($this->isManager(), 403, 'Unauthorized access: only the department manager can perform this action.');
    }
}
