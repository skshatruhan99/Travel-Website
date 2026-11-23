<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalAdminSetupController extends Controller
{
    public function create(Request $request)
    {
        $this->ensureLocalRequest($request);

        return view('auth.admin-setup');
    }

    public function store(Request $request)
    {
        $this->ensureLocalRequest($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::firstOrNew(['email' => $data['email']]);
        $user->name = $data['name'];
        $user->password = $data['password'];
        $user->forceFill(['is_admin' => true])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    private function ensureLocalRequest(Request $request): void
    {
        abort_unless(
            app()->environment('local') && in_array($request->ip(), ['127.0.0.1', '::1'], true),
            404
        );

        abort_if(User::where('is_admin', true)->exists(), 404);
    }
}