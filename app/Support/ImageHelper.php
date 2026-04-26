<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageHelper
{
    /**
     * Upload and process image.
     */
    public static function uploadImage(UploadedFile $file, string $folder, ?string $oldImage = null): ?string
    {
        try {
            if ($oldImage) {
                self::deleteImage($oldImage);
            }

            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;
            $path = "$folder/$filename";

            $image = Image::make($file);
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            Storage::disk('public')->makeDirectory($folder);
            Storage::disk('public')->put($path, (string) $image->encode());

            Log::info('Image uploaded successfully', [
                'path' => $path,
                'size' => Storage::disk('public')->size($path),
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to upload image', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            return null;
        }
    }

    /**
     * Delete image from storage.
     */
    public static function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Image deleted successfully', ['path' => $path]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete image', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);
        }

        return false;
    }

    /**
     * Get image URL.
     */
    public static function getImageUrl(?string $path, string $default = 'default/user.jpg'): string
    {
        $defaultUrl = self::resolveDefaultImage($default);

        if (empty($path)) {
            return $defaultUrl;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalized = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');

        return Storage::disk('public')->exists($normalized)
            ? Storage::url($normalized)
            : $defaultUrl;
    }

    /**
     * Create fallback image.
     */
    public static function createFallbackImage(string $text = 'No Image'): string
    {
        $img = Image::canvas(200, 200, '#eeeeee');
        $img->text($text, 100, 100, function ($font) {
            $font->file(public_path('fonts/OpenSans-Regular.ttf'));
            $font->size(16);
            $font->color('#787676');
            $font->align('center');
            $font->valign('middle');
        });

        $tempPath = storage_path('app/public/temp-' . Str::random(10) . '.jpg');
        $img->save($tempPath);

        return $tempPath;
    }

    private static function resolveDefaultImage(string $default): string
    {
        if (filter_var($default, FILTER_VALIDATE_URL)) {
            return $default;
        }

        $normalized = ltrim(str_replace(['storage/', 'public/'], '', $default), '/');
        if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
            return Storage::url($normalized);
        }

        return asset($default);
    }
}
