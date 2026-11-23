<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'users' => User::latest()->paginate(20),
            'adminCount' => User::where('is_admin', true)->count(),
        ]);
    }

    public function updateAdminRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        $makeAdmin = (bool) $data['is_admin'];
        $demotingSelf = ! $makeAdmin && $user->is($request->user());

        if ($makeAdmin && ! $user->is_admin) {
            $passwordData = $request->validateWithBag('adminRole'.$user->id, [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user->password = Hash::make($passwordData['password']);
        }

        if (! $makeAdmin && $user->is_admin) {
            if (User::where('is_admin', true)->count() <= 1) {
                return back()->withErrors(['admin_role' => 'The last admin account cannot be removed. Add another admin first.']);
            }

            if ($demotingSelf) {
                $request->validate([
                    'current_password' => ['required', 'current_password'],
                ]);
            }
        }

        $user->forceFill(['is_admin' => $makeAdmin])->save();

        if ($demotingSelf) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Admin access removed. Log in again to continue as a regular user.');
        }

        return back()->with('success', $makeAdmin
            ? $user->name . ' is now an admin.'
            : $user->name . ' is no longer an admin.');
    }
}
