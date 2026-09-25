<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountApprovalNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $allowAdmin = auth()->check() && auth()->user()->role === 'admin';
        return view('auth.register', ['allowAdmin' => $allowAdmin]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,property_custodian,teacher'],
        ]);

        // Prevent non-admins from creating admin users even if the form is tampered with
        $requestedRole = $request->role;
        if ($requestedRole === 'admin' && !(auth()->check() && auth()->user()->role === 'admin')) {
            return redirect()->back()->withErrors(['role' => 'Creating admin accounts is restricted'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $requestedRole,
            'is_active' => false,
        ]);

        event(new Registered($user));

        User::where('role', 'admin')->get()->each(function ($admin) use ($user) {
            $admin->notify(new AccountApprovalNotification($user, 'pending'));
        });

        if (auth()->check()) {
            return redirect()->route('admin.users')->with('success', 'User created successfully and is pending approval.');
        }

        return redirect()->route('login')->with('status', 'Your account is pending admin approval.');
    }
}
