<?php

namespace App\Support;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AvatarStorage
{
    private const USER_DIRECTORY = 'admin-photos';
    private const PEGAWAI_DIRECTORY = 'pegawai/foto-profil';
    private const PEGAWAI_ADDITIONAL_DIRECTORIES = [
        'pegawai',
        'pegawai/foto',
    ];

    public static function normalizeUserAvatars(): void
    {
        try {
            self::ensureDirectory(self::USER_DIRECTORY);

            User::whereNotNull('gambar')
                ->orderBy('id')
                ->each(function (User $user): void {
                    if (self::isExternalUrl($user->gambar)) {
                        return;
                    }

                    $resolved = self::resolveStoragePath($user->gambar, [self::USER_DIRECTORY]);

                    if (!$resolved || !file_exists(public_path('storage/' . $resolved))) {
                        if ($user->gambar !== null) {
                            $user->forceFill(['gambar' => null])->save();
                        }
                        return;
                    }

                    $converted = self::ensureJpeg($resolved);

                    if ($user->gambar !== $converted) {
                        $user->forceFill(['gambar' => $converted])->save();
                    }
                });
        } catch (\Exception $e) {
            Log::error('Error normalizing user avatars: ' . $e->getMessage());
        }
    }

    public static function normalizePegawaiAvatars(): void
    {
        try {
            self::ensureDirectory(self::PEGAWAI_DIRECTORY);

            Pegawai::whereNotNull('gambar')
                ->orderBy('id')
                ->each(function (Pegawai $pegawai): void {
                    $gambar = $pegawai->gambar;
                    if (self::isExternalUrl($gambar)) {
                        return;
                    }

                    $resolved = self::resolveStoragePath($gambar, array_merge(
                        [self::PEGAWAI_DIRECTORY],
                        self::PEGAWAI_ADDITIONAL_DIRECTORIES
                    ));

                    if (!$resolved || !file_exists(public_path('storage/' . $resolved))) {
                        if ($gambar !== null) {
                            $pegawai->forceFill(['gambar' => null])->save();
                        }
                        return;
                    }

                    $converted = self::ensureJpeg($resolved);

                    if ($pegawai->gambar !== $converted) {
                        $pegawai->forceFill(['gambar' => $converted])->save();
                    }
                });
        } catch (\Exception $e) {
            Log::error('Error normalizing pegawai avatars: ' . $e->getMessage());
        }
    }

    public static function storeUserAvatar(UploadedFile $file): string
    {
        self::ensureDirectory(self::USER_DIRECTORY);
        return self::storeAsJpeg($file, self::USER_DIRECTORY);
    }

    public static function storePegawaiAvatar(UploadedFile $file): string
    {
        self::ensureDirectory(self::PEGAWAI_DIRECTORY);
        return self::storeAsJpeg($file, self::PEGAWAI_DIRECTORY);
    }

    public static function deleteUserAvatar(?string $path): void
    {
        self::deletePath($path, [self::USER_DIRECTORY]);
    }

    public static function deletePegawaiAvatar(?string $path): void
    {
        self::deletePath($path, array_merge(
            [self::PEGAWAI_DIRECTORY],
            self::PEGAWAI_ADDITIONAL_DIRECTORIES
        ));
    }

    private static function deletePath(?string $path, array $fallbackDirectories = []): void
    {
        if (!$path || self::isExternalUrl($path)) {
            return;
        }

        try {
            $resolved = self::resolveStoragePath($path, $fallbackDirectories);
            if ($resolved && file_exists(public_path('storage/' . $resolved))) {
                Storage::disk('public')->delete($resolved);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete avatar: ' . $e->getMessage());
        }
    }

    private static function ensureDirectory(string $directory): void
    {
        try {
            if (!file_exists(public_path('storage/' . $directory))) {
                Storage::disk('public')->makeDirectory($directory);
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure directory: ' . $e->getMessage());
        }
    }

    private static function storeAsJpeg(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');

        try {
            $imageData = @file_get_contents($file->getRealPath());
            $canConvert = function_exists('imagecreatefromstring') && function_exists('imagejpeg');
            $imageResource = ($canConvert && $imageData !== false) ? @imagecreatefromstring($imageData) : false;

            if ($canConvert && (is_resource($imageResource) || $imageResource instanceof \GdImage)) {
                ob_start();
                imagejpeg($imageResource, null, 90);
                $jpegBinary = ob_get_clean();
                if (is_resource($imageResource) || $imageResource instanceof \GdImage) {
                    imagedestroy($imageResource);
                }

                if ($jpegBinary !== false && $jpegBinary !== '') {
                    $filename = Str::uuid()->toString() . '.jpg';
                    $path = $directory . '/' . $filename;
                    Storage::disk('public')->put($path, $jpegBinary);
                    return $path;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error converting image to JPEG: ' . $e->getMessage());
        }

        return $file->store($directory, 'public');
    }

    private static function ensureJpeg(string $path): string
    {
        try {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg'], true)) {
                return $path;
            }

            $fullPath = public_path('storage/' . $path);
            if (!file_exists($fullPath)) {
                return $path;
            }

            $canConvert = function_exists('imagecreatefromstring') && function_exists('imagejpeg');
            $imageData = @file_get_contents($fullPath);
            $imageResource = ($canConvert && $imageData !== false) ? @imagecreatefromstring($imageData) : false;

            if (!$canConvert || !(is_resource($imageResource) || $imageResource instanceof \GdImage)) {
                return $path;
            }

            ob_start();
            imagejpeg($imageResource, null, 90);
            $jpegBinary = ob_get_clean();
            if (is_resource($imageResource) || $imageResource instanceof \GdImage) {
                imagedestroy($imageResource);
            }

            if ($jpegBinary === false || $jpegBinary === '') {
                return $path;
            }

            $jpegPath = preg_replace('/\.[^.]+$/', '.jpg', $path) ?: ($path . '.jpg');

            Storage::disk('public')->put($jpegPath, $jpegBinary);
            Storage::disk('public')->delete($path);

            return $jpegPath;
        } catch (\Exception $e) {
            Log::error('Error ensuring JPEG: ' . $e->getMessage());
            return $path;
        }
    }

    private static function resolveStoragePath(?string $path, array $fallbackDirectories = []): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (preg_match('/^https?:\/\//i', $normalized)) {
            return null;
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (file_exists(public_path('storage/' . $normalized))) {
            return $normalized;
        }

        $basename = basename($normalized);
        foreach ($fallbackDirectories as $directory) {
            $candidate = trim($directory . '/' . $basename, '/');
            if (file_exists(public_path('storage/' . $candidate))) {
                return $candidate;
            }
        }

        return null;
    }

    private static function isExternalUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return (bool) preg_match('/^https?:\/\//i', $value);
    }
}
