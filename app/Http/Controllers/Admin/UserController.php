<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\AvatarStorage;
use App\Support\ActivityLogger;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->is_admin, 403);

        AvatarStorage::normalizeUserAvatars();
        AvatarStorage::normalizePegawaiAvatars();

        $activeTab = $request->query('tab', 'users');
        return view('admin.users.index', [
            'users' => User::orderByDesc('created_at')->get(),
            'pegawais' => Pegawai::with(['creator', 'updater'])->orderBy('nama')->get(),
            'activeTab' => in_array($activeTab, ['users', 'pegawai'], true) ? $activeTab : 'users',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $path = null;
        if ($request->hasFile('gambar')) {
            AvatarStorage::normalizeUserAvatars();
            $path = AvatarStorage::storeUserAvatar($request->file('gambar'));
        }

        $isActive = $request->has('is_active')
            ? $request->boolean('is_active')
            : true;

        $isAdmin = $request->has('is_admin')
            ? $request->boolean('is_admin')
            : false;

        $user = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'nomor_hp' => $validated['nomor_hp'] ?? null,
            'gambar' => $path,
            'is_active' => $isActive,
            'is_admin' => $isAdmin,
        ]);

        $user->forceFill(['last_login' => null])->save();

        ActivityLogger::log('admin.created', $user, 'Admin portal ditambahkan', [
            'nama' => $user->nama,
            'email' => $user->email,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'users'])
            ->with('status', 'Admin berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'is_admin' => ['nullable', 'boolean'],
            'remove_gambar' => ['nullable', 'boolean'],
        ]);

        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_hp' => $validated['nomor_hp'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $user->is_active,
            'is_admin' => $request->has('is_admin') ? $request->boolean('is_admin') : $user->is_admin,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->boolean('remove_gambar') && $user->gambar) {
            AvatarStorage::deleteUserAvatar($user->gambar);
            $data['gambar'] = null;
        }

        if ($request->hasFile('gambar')) {
            AvatarStorage::normalizeUserAvatars();
            AvatarStorage::deleteUserAvatar($user->gambar);
            $data['gambar'] = AvatarStorage::storeUserAvatar($request->file('gambar'));
        }

        $user->update($data);

        ActivityLogger::log('admin.updated', $user, 'Data admin diperbarui', [
            'nama' => $user->nama,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'users'])
            ->with('status', 'Admin berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('admin.users.index', ['tab' => 'users'])
                ->withErrors(['delete' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        if ($user->gambar) {
            AvatarStorage::deleteUserAvatar($user->gambar);
        }

        $userName = $user->nama;
        $userEmail = $user->email;

        $user->delete();

        ActivityLogger::log('admin.deleted', $user, 'Admin dihapus', [
            'nama' => $userName,
            'email' => $userEmail,
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'users'])
            ->with('status', 'Admin berhasil dihapus.');
    }
}


