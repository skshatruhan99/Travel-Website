@extends('admin.layout')

@section('title', 'Journal | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">EDITORIAL</span><h1>Journal</h1><p>Write and publish travel stories.</p></div><a href="{{ route('admin.journal.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> New article</a></div>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Article</th><th>Category</th><th>Published</th><th>Actions</th></tr></thead><tbody>
	@forelse($articles as $article)
		<tr><td><span class="customer-name">{{ $article->title }}</span><small class="customer-email">/{{ $article->slug }}</small></td><td>{{ $article->category ?: 'Uncategorized' }}</td><td><span class="admin-status {{ $article->is_published ? 'status-confirmed' : 'status-pending' }}">{{ $article->is_published ? 'Published' : 'Draft' }}</span></td><td class="admin-actions-cell"><a class="admin-role-button" href="{{ route('admin.journal.edit', $article) }}">Edit</a>@if($article->is_published)<a class="admin-role-button" href="{{ route('journal.article', $article->slug) }}" target="_blank" rel="noopener">View</a>@endif<form action="{{ route('admin.journal.destroy', $article) }}" method="POST" onsubmit="return confirm('Delete this article?');">@csrf @method('DELETE')<button class="admin-role-button" type="submit">Delete</button></form></td></tr>
	@empty
		<tr><td colspan="4" class="admin-empty">No articles yet.</td></tr>
	@endforelse
</tbody></table></div><div class="admin-pagination">{{ $articles->links() }}</div></section></section>
@endsection
