<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $data['email']]);

        if ($subscriber->is_active) {
            return back()->with('newsletter_success', 'You are already subscribed to our newsletter.');
        }

        $subscriber->is_active = true;
        $subscriber->save();

        return back()->with('newsletter_success', 'Thanks for subscribing to our newsletter.');
    }
}
