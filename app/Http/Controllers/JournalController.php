<?php

namespace App\Http\Controllers;

use App\Models\JournalPost;

class JournalController extends Controller
{
    public function index()
    {
        return view('pages.journal', [
            'articles' => JournalPost::where('is_published', true)->latest('published_at')->paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        $article = JournalPost::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('pages.article', compact('article'));
    }
}
