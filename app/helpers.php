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
        return ImageHelper::getUrl($path, $default);
    }
}
