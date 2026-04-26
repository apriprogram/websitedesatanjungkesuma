<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Serve a user's avatar image from storage or return 404 if no image.
     *
     * @param Request $request
     * @param int|string $id
     */
    public function userAvatar(Request $request, User $user)
    {
        $gambar = $user->gambar ?? null;

        if (!$gambar) {
            abort(404);
        }

        return $this->serveImageOrDefault($gambar);
    }

    /**
     * Serve a pegawai's avatar image from storage or return 404 if no image.
     *
     * @param Request $request
     * @param int|string $id
     */
    public function pegawaiAvatar(Request $request, Pegawai $pegawai)
    {
        $gambar = $pegawai->gambar ?? null;

        if (!$gambar) {
            abort(404);
        }

        return $this->serveImageOrDefault($gambar);
    }

    /**
     * Helper to serve an image path or external URL.
     *
     * @param string|null $path
     */
    private function serveImageOrDefault(?string $path)
    {
        // External URLs -> redirect
        if ($path && preg_match('#^https?://#i', $path)) {
            return redirect()->away($path);
        }

        // Normalize storage path (strip leading storage/ or public/)
        if ($path) {
            $normalized = ltrim(Str::replace('\\', '/', $path), '/');
            if (Str::startsWith($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }
            if (Str::startsWith($normalized, 'public/')) {
                $normalized = substr($normalized, 7);
            }

            if (Storage::disk('public')->exists($normalized)) {
                $full = Storage::disk('public')->path($normalized);
                return response()->file($full, [
                    'Cache-Control' => 'public, max-age=604800, immutable',
                ]);
            }
        }

        // Return 404 if no image found
        abort(404);
    }
}
