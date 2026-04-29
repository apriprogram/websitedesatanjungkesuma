<?php

use App\Support\ImageHelper;

if (!function_exists('image_url')) {
    /**
     * Get safe image URL for cPanel hosting.
     *
     * @param string|null $path
     * @param string|null $default
     * @return string
     */
    function image_url($path, $default = null)
    {
        return $default !== null 
            ? ImageHelper::getImageUrl($path, $default) 
            : ImageHelper::getImageUrl($path);
    }
}

if (!function_exists('safe_store')) {
    /**
     * Store uploaded file safely without 'finfo' dependency.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string
     */
    function safe_store($file, $directory)
    {
        $directory = trim($directory, '/');
        $extension = $file->getClientOriginalExtension();
        if (empty($extension)) {
            $extension = 'bin'; // fallback extension
        }
        $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
        $file->move(public_path('storage/' . $directory), $filename);
        return $directory . '/' . $filename;
    }
}
