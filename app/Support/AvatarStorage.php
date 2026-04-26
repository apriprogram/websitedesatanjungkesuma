<?php

namespace App\Support;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        self::ensureDirectory(self::USER_DIRECTORY);

        User::whereNotNull('gambar')
            ->orderBy('id')
            ->each(function (User $user): void {
                if (self::isExternalUrl($user->gambar)) {
                    return;
                }

                $resolved = self::resolveStoragePath($user->gambar, [self::USER_DIRECTORY]);

                if (!$resolved || !Storage::disk('public')->exists($resolved)) {
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
    }

    public static function normalizePegawaiAvatars(): void
    {
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

                if (!$resolved || !Storage::disk('public')->exists($resolved)) {
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

        $resolved = self::resolveStoragePath($path, $fallbackDirectories);

        if ($resolved && Storage::disk('public')->exists($resolved)) {
            Storage::disk('public')->delete($resolved);
        }
    }

    private static function ensureDirectory(string $directory): void
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }
    }

    private static function storeAsJpeg(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $disk = Storage::disk('public');

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
                $disk->put($path, $jpegBinary);
                return $path;
            }
        }

        return $file->store($directory, 'public');
    }

    private static function ensureJpeg(string $path): string
    {
        $disk = Storage::disk('public');

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            return $path;
        }

        $fullPath = $disk->path($path);
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

        $disk->put($jpegPath, $jpegBinary);
        $disk->delete($path);

        return $jpegPath;
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

        $disk = Storage::disk('public');

        if ($disk->exists($normalized)) {
            return $normalized;
        }

        $basename = basename($normalized);
        foreach ($fallbackDirectories as $directory) {
            $candidate = trim($directory . '/' . $basename, '/');
            if ($disk->exists($candidate)) {
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
