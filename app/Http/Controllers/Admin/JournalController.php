<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JournalController extends Controller
{
    public function index()
    {
        return view('admin.journal.index', ['articles' => JournalPost::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.journal.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;
        JournalPost::create($data);

        return redirect()->route('admin.journal.index')->with('success', 'Article created successfully.');
    }

    public function edit(JournalPost $journal)
    {
        return view('admin.journal.edit', ['article' => $journal]);
    }

    public function update(Request $request, JournalPost $journal)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $journal);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? ($journal->published_at ?? now()) : null;
        $journal->update($data);

        return redirect()->route('admin.journal.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(JournalPost $journal)
    {
        $journal->delete();

        return redirect()->route('admin.journal.index')->with('success', 'Article deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:50000'],
            'category' => ['nullable', 'string', 'max:100'],
            'featured_image' => ['nullable', 'url', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $title, ?JournalPost $article = null): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (JournalPost::where('slug', $slug)->when($article, fn ($query) => $query->whereKeyNot($article->id))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
