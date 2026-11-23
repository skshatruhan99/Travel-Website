<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->wishlistItems()->with('activity')->latest()->get();

        return view('pages.wishlist', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_type' => ['required', 'string', 'max:50'],
            'item_id' => ['required', 'integer', 'min:1'],
        ]);

        Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'item_type' => $data['item_type'],
            'item_id' => $data['item_id'],
        ]);

        return back()->with('success', 'Saved to your wishlist.');
    }

    public function destroy(Request $request, Wishlist $wishlist)
    {
        abort_unless($wishlist->user_id === $request->user()->id, 403);
        $wishlist->delete();

        return back()->with('success', 'Removed from your wishlist.');
    }
}