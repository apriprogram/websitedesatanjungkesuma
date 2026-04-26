<?php

namespace App\Models\Traits;

use App\Support\ImageHelper;

trait HasProfileImage
{
    /**
     * Get image URL attribute
     */
    public function getImageUrlAttribute(): string
    {
        return ImageHelper::getImageUrl($this->gambar, $this->defaultImage());
    }

    /**
     * Handle image upload
     */
    public function uploadImage($file): bool
    {
        if (!$file) {
            return false;
        }

        $path = ImageHelper::uploadImage($file, $this->getImageFolder());
        if ($path) {
            $this->gambar = $path;
            return $this->save();
        }

        return false;
    }

    /**
     * Delete image
     */
    public function deleteImage(): bool
    {
        if ($this->gambar) {
            if (ImageHelper::deleteImage($this->gambar)) {
                $this->gambar = null;
                return $this->save();
            }
        }
        return false;
    }

    /**
     * Get image folder
     */
    protected function getImageFolder(): string
    {
        return 'users';
    }

    protected function defaultImage(): string
    {
        return 'default/user.jpg';
    }
}
