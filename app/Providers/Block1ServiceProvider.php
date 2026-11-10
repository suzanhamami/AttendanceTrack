<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Providers;

use App\Http\Middleware\Block1LoginGuard;
use App\Support\Block1\Access;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Wires Block 1 into the application without editing shared files
 * (routes/web.php, bootstrap/app.php, AuthController, User model):
 *   1. Permissions from the `permissions` table are exposed as Gate abilities
 *      -> usable as `can:users.delete` middleware, @can in Blade, $user->can() in PHP.
 *   2. Login requirements of the spec, for whichever login controller the project uses:
 *      5-attempt lock, a specific error per failed step, and user id / department / role in the session.
 *   3. Block 1 routes (routes/users.php), loaded after the application's own routes.
 */
class Block1ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Catalog permissions are answered from the permissions table; other abilities are left alone.
        Gate::before(function ($user, string $ability) {
            if (in_array($ability, Access::catalog(), true)) {
                return Access::has($user, $ability);
            }
            return null;
        });

        // 2a. Spec: "store the user id, department and role in the session" after login.
        Event::listen(Login::class, function (Login $event) {
            if (app()->bound('session.store') && request()->hasSession()) {
                request()->session()->put([
                    'user_id'       => $event->user->user_id,
                    'department_id' => $event->user->department_id,
                    'role'          => $event->user->role?->role_name,
                ]);
            }
        });

        // 2b. Login checks run before the login controller (see Block1LoginGuard).
        $this->app->make(Kernel::class)->appendMiddlewareToGroup('web', Block1LoginGuard::class);

        // 3. Block 1 routes. They are registered here, BEFORE routes/web.php is loaded, so when the
        //    project's web.php defines its own /login, /logout (shared AuthController) those replace the
        //    Block 1 fallback login automatically (a later route with the same method + URI wins).
        if (!$this->app->routesAreCached()) {
            Route::middleware('web')->group(base_path('routes/users.php'));
        }
    }
}
