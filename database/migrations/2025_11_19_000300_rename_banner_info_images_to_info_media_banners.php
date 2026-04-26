<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('banner_info_images')) {
            Schema::rename('banner_info_images', 'info_media_banners');
        }
    }

    public function down()
    {
        if (Schema::hasTable('info_media_banners')) {
            Schema::rename('info_media_banners', 'banner_info_images');
        }
    }
};
