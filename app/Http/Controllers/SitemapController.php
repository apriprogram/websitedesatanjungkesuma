<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $news = News::where('status', 'published')->orderBy('published_at', 'desc')->get();

        return response()->view('sitemap', [
            'news' => $news
        ])->header('Content-Type', 'text/xml');
    }
}
