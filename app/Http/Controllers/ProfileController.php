<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $oldAvatar = $user->avatar;
        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            if (! is_string($avatarPath)) {
                throw new \RuntimeException('The profile photo could not be stored.');
            }
        }

        $removeAvatar = $avatarPath === null && (bool) ($data['remove_avatar'] ?? false);
        unset($data['avatar'], $data['remove_avatar']);

        if ($avatarPath !== null) {
            $data['avatar'] = $avatarPath;
        } elseif ($removeAvatar) {
            $data['avatar'] = null;
        }

        $user->update($data);

        if (($avatarPath !== null || $removeAvatar)
            && is_string($oldAvatar)
            && str_starts_with($oldAvatar, 'avatars/')
            && ! Storage::disk('public')->delete($oldAvatar)) {
            throw new \RuntimeException('The previous profile photo could not be removed.');
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password updated successfully.');
    }
}
