<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ContentUnlock;
use App\Services\ContentUnlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Client's request (2026-09-21/22): admin AND editor can set a price on a photo or album
 * (answer #1: "Si admin si editor sa poata stabili pretul... De ex punem 1 euro la o poza/video
 * iar la alta 3"). No new admin screen for this - admin/editor already land on any profile's
 * existing /profile/{username}/photos and /profile/{username}/albums pages (isAdmin() satisfies
 * hasPermission()), which now show a small inline price form on each item when Auth::user()
 * isAdmin() - see the price-edit overlay in those two blade files. This controller is just the
 * thin save endpoint for that form.
 */
final class AdminContentPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request, ContentUnlockService $unlocks)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'type' => ['required', 'in:' . ContentUnlock::TYPE_IMAGE . ',' . ContentUnlock::TYPE_ALBUM],
            'id' => ['required', 'integer'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999'],
            'price_credits' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $item = $validated['type'] === ContentUnlock::TYPE_IMAGE
            ? $unlocks->findImage((int) $validated['id'])
            : $unlocks->findAlbum((int) $validated['id']);

        $item->price = $validated['price'];
        $item->price_credits = $validated['price_credits'];
        $item->save();

        return response()->json([
            'success' => true,
            'price' => (float) $item->price,
            'price_credits' => (int) $item->price_credits,
        ]);
    }
}
