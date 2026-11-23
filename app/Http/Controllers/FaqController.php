<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        return view('pages.faq', [
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
