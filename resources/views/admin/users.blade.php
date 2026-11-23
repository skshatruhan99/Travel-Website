@extends('admin.layout')

@section('title', 'Admin Users - NpAdvanture')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">ACCOUNT ACCESS</span><h1>Users</h1><p>Manage customer accounts and administrator access.</p></div></div>
    <section class="admin-panel">
        @if($errors->has('admin_role'))
            <div class="admin-alert admin-alert-error" role="alert"><i class="fa-solid fa-triangle-exclamation"></i>{{ $errors->first('admin_role') }}</div>
        @endif
        <div class="admin-table-wrap">
            <table class="admin-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Access</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td><span class="admin-status {{ $user->is_admin ? 'status-confirmed' : '' }}">{{ $user->is_admin ? 'Admin' : 'User' }}</span></td>
                                <td>
                                    <form action="{{ route('admin.users.admin', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_admin" value="{{ $user->is_admin ? 0 : 1 }}">
                                        @if($user->is_admin && $user->is(auth()->user()))
                                            <div class="admin-role-self">
                                                <label class="sr-only" for="current-password-{{ $user->id }}">Confirm your password to remove admin access</label>
                                                <input id="current-password-{{ $user->id }}" name="current_password" type="password" class="admin-role-password" placeholder="Your password" autocomplete="current-password" @required($adminCount > 1) @disabled($adminCount <= 1)>
                                                <button class="admin-role-button" type="submit" @disabled($adminCount <= 1)>Remove my admin access</button>
                                            </div>
                                            @error('current_password')
                                                <small class="admin-role-note">{{ $message }}</small>
                                            @enderror
                                            @if($adminCount <= 1)
                                                <small class="admin-role-note">Add another admin before removing your access.</small>
                                            @endif
                                        @else
                                            @if(!$user->is_admin)
                                                <div class="admin-role-promotion">
                                                    <label class="sr-only" for="admin-password-{{ $user->id }}">Set a password for {{ $user->name }}</label>
                                                    <input id="admin-password-{{ $user->id }}" name="password" type="password" class="admin-role-password" placeholder="Set password" minlength="8" autocomplete="new-password" required>
                                                    <label class="sr-only" for="admin-password-confirmation-{{ $user->id }}">Confirm the new admin password for {{ $user->name }}</label>
                                                    <input id="admin-password-confirmation-{{ $user->id }}" name="password_confirmation" type="password" class="admin-role-password" placeholder="Confirm password" minlength="8" autocomplete="new-password" required>
                                                </div>
                                                @error('password', 'adminRole'.$user->id)
                                                    <small class="admin-role-note">{{ $message }}</small>
                                                @enderror
                                                <small class="admin-role-note">Share this password with the new admin securely.</small>
                                            @endif
                                            <button class="admin-role-button" type="submit" @disabled($user->is_admin && $adminCount <= 1)>
                                                {{ $user->is_admin ? 'Remove admin' : 'Make admin' }}
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-empty">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="admin-pagination">{{ $users->links() }}</div>
    </section>
</section>
@endsection
