<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

/**
 * Backs the unified email-first auth form (components/unified-auth-form.blade.php,
 * config('services.unified_login.enabled')): the visitor types only their email first, this
 * says whether they already have an account, and the form then reveals the password field
 * (login) or the rest of the registration fields - without needing them to pick "Register" or
 * "Login" themselves. Doesn't touch auth state - the actual login/register POST still goes to
 * the normal LoginController/RegisterController, unchanged.
 */
class EmailCheckController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function check(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // gender=male mirrors LoginController::credentials()'s own constraint on the normal
        // form - this site's real (non-admin-managed) registrants are exclusively male, so an
        // email that only matches a female/admin-managed profile should route to registration,
        // not to a login step whose password that visitor could never actually have.
        $exists = User::where('email', $request->email)->where('gender', 'male')->exists();

        return response()->json(['exists' => $exists]);
    }
}
