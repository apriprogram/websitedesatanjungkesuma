<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Support\AvatarStorage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'remove_gambar' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_hp' => $validated['nomor_hp'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_gambar') && $user->gambar) {
            AvatarStorage::deleteUserAvatar($user->gambar);
            $user->gambar = null;
        }

        if ($request->hasFile('gambar')) {
            AvatarStorage::normalizeUserAvatars();
            AvatarStorage::deleteUserAvatar($user->gambar);
            $user->gambar = AvatarStorage::storeUserAvatar($request->file('gambar'));
        }

        $user->save();

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }
}
