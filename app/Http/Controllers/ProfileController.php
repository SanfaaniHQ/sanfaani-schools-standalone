<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\SuperAdminAccountProtectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user->avatar_path);
            $extension = strtolower((string) $request->file('avatar')->getClientOriginalExtension());
            $user->avatar_path = $request->file('avatar')->storeAs(
                'avatars/'.$user->id,
                'avatar-'.Str::uuid().'.'.$extension,
                'public'
            );
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, SuperAdminAccountProtectionService $superAdminProtection): RedirectResponse
    {
        $user = $request->user();

        // Prevent school users from deleting their own accounts
        if ($user->hasAnyRole(['school_admin', 'result_officer', 'teacher'])) {
            abort(403, 'You cannot delete your own account. Contact your administrator.');
        }

        $superAdminProtection->assertCanDelete($user, $user, 'userDeletion', $request);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteAvatar(?string $path): void
    {
        $path = str_replace('\\', '/', ltrim((string) $path, '/'));

        if ($path !== '' && Str::startsWith($path, 'avatars/') && ! Str::contains($path, ['..', '.env'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
