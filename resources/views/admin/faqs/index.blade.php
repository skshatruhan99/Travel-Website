@extends('admin.layout')

@section('title', 'FAQs | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page"><div class="admin-page-heading"><div><span class="admin-eyebrow">HELP CONTENT</span><h1>FAQs</h1><p>Manage answers shown on the public FAQ page.</p></div><a href="{{ route('admin.faqs.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add FAQ</a></div>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Question</th><th>Category</th><th>Order</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
	@forelse($faqs as $faq)
		<tr><td><span class="customer-name">{{ $faq->question }}</span><small class="customer-email">{{ \Illuminate\Support\Str::limit($faq->answer, 90) }}</small></td><td>{{ $faq->category ?: 'General' }}</td><td>{{ $faq->sort_order }}</td><td><span class="admin-status {{ $faq->is_active ? 'status-confirmed' : '' }}">{{ $faq->is_active ? 'Active' : 'Hidden' }}</span></td><td class="admin-actions-cell"><a class="admin-role-button" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a><form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Delete this FAQ?');">@csrf @method('DELETE')<button class="admin-role-button" type="submit">Delete</button></form></td></tr>
	@empty
		<tr><td colspan="5" class="admin-empty">No FAQs have been added.</td></tr>
	@endforelse
</tbody></table></div><div class="admin-pagination">{{ $faqs->links() }}</div></section></section>
@endsection
