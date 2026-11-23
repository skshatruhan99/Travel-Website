<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $subscribers = NewsletterSubscriber::query()
            ->when(isset($data['status']), fn ($query) => $query->where('is_active', $data['status'] === 'active'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.newsletter-subscribers.index', compact('subscribers'));
    }

    public function updateStatus(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $subscriber->update(['is_active' => $data['is_active']]);

        return back()->with('success', 'Newsletter subscription status updated.');
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Newsletter subscriber deleted.');
    }
}
