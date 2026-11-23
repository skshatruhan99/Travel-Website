<form action="{{ $article ? route('admin.journal.update', $article) : route('admin.journal.store') }}" method="POST" class="admin-form">
    @csrf
    @if($article) @method('PUT') @endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-form-grid">
        <div class="form-group admin-form-wide"><label for="title">Title</label><input id="title" name="title" class="form-control" value="{{ old('title', $article?->title) }}" required></div>
        <div class="form-group"><label for="category">Category</label><input id="category" name="category" class="form-control" value="{{ old('category', $article?->category) }}"></div>
        <div class="form-group"><label for="featured_image">Featured image URL</label><input id="featured_image" name="featured_image" type="url" class="form-control" value="{{ old('featured_image', $article?->featured_image) }}"></div>
        <div class="form-group admin-form-wide"><label for="excerpt">Excerpt</label><textarea id="excerpt" name="excerpt" class="form-control" rows="2">{{ old('excerpt', $article?->excerpt) }}</textarea></div>
        <div class="form-group admin-form-wide"><label for="content">Article content</label><textarea id="content" name="content" class="form-control" rows="12" required>{{ old('content', $article?->content) }}</textarea></div>
    </div>
    <div class="admin-form-toggles"><label><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article?->is_published ?? false))> Publish article</label></div>
    <div class="admin-form-actions"><button class="admin-primary-action" type="submit">{{ $article ? 'Save article' : 'Create article' }}</button><a href="{{ route('admin.journal.index') }}" class="admin-role-button">Cancel</a></div>
</form>