<form action="{{ $faq ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" method="POST" class="admin-form">
    @csrf
    @if($faq) @method('PUT') @endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <div class="admin-form-grid">
        <div class="form-group"><label for="category">Category</label><input id="category" name="category" class="form-control" value="{{ old('category', $faq?->category) }}"></div>
        <div class="form-group"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $faq?->sort_order ?? 0) }}" required></div>
        <div class="form-group admin-form-wide"><label for="question">Question</label><input id="question" name="question" class="form-control" value="{{ old('question', $faq?->question) }}" required></div>
        <div class="form-group admin-form-wide"><label for="answer">Answer</label><textarea id="answer" name="answer" class="form-control" rows="5" required>{{ old('answer', $faq?->answer) }}</textarea></div>
    </div>
    <div class="admin-form-toggles"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq?->is_active ?? true))> Show on website</label></div>
    <div class="admin-form-actions"><button class="admin-primary-action" type="submit">{{ $faq ? 'Save FAQ' : 'Create FAQ' }}</button><a href="{{ route('admin.faqs.index') }}" class="admin-role-button">Cancel</a></div>
</form>