<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ContentUnlock;
use App\Services\ContentUnlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Client's request (2026-09-21/22): "Poze si albume cu pret" - paying (credits or real money,
 * per the admin's CONTENT_UNLOCK_PRICE_MODE choice) to unlock a priced photo or album, wherever
 * it's shown (newsfeed, profile gallery, albums). See ContentUnlockService for the actual
 * purchase logic - this controller is just the thin HTTP layer, same split as BoostController.
 */
final class ContentUnlockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Credits-mode purchase - instant, AJAX (mirrors BoostController::activate()).
     */
    public function purchase(Request $request, ContentUnlockService $unlocks)
    {
        abort_if(! $unlocks->enabled(), 404);

        if ($unlocks->priceMode() === 'money') {
            return response()->json(['success' => false, 'error' => 'wrong_mode'], 409);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:' . ContentUnlock::TYPE_IMAGE . ',' . ContentUnlock::TYPE_ALBUM],
            'id' => ['required', 'integer'],
        ]);

        $error = $unlocks->purchaseWithCredits(Auth::user(), $validated['type'], (int) $validated['id']);

        if ($error !== null) {
            return response()->json([
                'success' => false,
                'error' => $error,
                'credits' => Auth::user()->credits,
            ], $error === 'not_enough_credits' ? 402 : 422);
        }

        return response()->json(['success' => true, 'credits' => Auth::user()->fresh()->credits]);
    }

    /**
     * Money-mode purchase - a plain page navigation, same shape as BoostController::checkout()
     * (creates a hidden Pack, sends the browser through the normal /payments flow).
     */
    public function checkout(Request $request, ContentUnlockService $unlocks)
    {
        abort_if(! $unlocks->enabled(), 404);

        if ($unlocks->priceMode() !== 'money') {
            abort(409);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:' . ContentUnlock::TYPE_IMAGE . ',' . ContentUnlock::TYPE_ALBUM],
            'id' => ['required', 'integer'],
        ]);

        $type = $validated['type'];
        $id = (int) $validated['id'];

        $price = $unlocks->priceOf($type, $id);

        if ($price <= 0) {
            abort(404);
        }

        $result = $unlocks->startMoneyCheckout($type, $id);

        if ($result === null) {
            return redirect()->back()->with('status', 'Plata cu bani reali nu este disponibila momentan. Incearca din nou mai tarziu.');
        }

        if ($result['activeProviders']->count() === 1) {
            return redirect()->route('new_payment', [
                'pack_id' => $result['pack']->id,
                'payment_method' => $result['activeProviders']->first(),
            ]);
        }

        // Same shared "more than one provider active" picker Boost uses - fully generic already
        // (just pack/price/activeProviders), no content-unlock-specific view needed.
        return view('boost_checkout_select', [
            'title' => l('Select Payment method'),
            'pack' => $result['pack'],
            'price' => $price,
            'activeProviders' => $result['activeProviders'],
        ]);
    }
}
