<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Groups the payment-provider settings (Stripe/Wire-transfer/CCBill/PayPal/CentralPay -
     * previously ~20 ungrouped rows sitting one after another in "General", easy to lose track
     * of which field belongs to which provider) under their own category cards, same mechanism
     * as the earlier Boost settings grouping (see the migration that added `settings.category`).
     * Each provider's own *_ACTIVE toggle (already `type = 'toggle'`) becomes that card's
     * enabler switch once admin/settings.blade.php's enabler condition is extended to also
     * match `_ACTIVE`, not just `_ENABLED` - so switching a provider off now visibly grays out
     * its own credentials instead of leaving everything looking equally "live".
     */
    public function up(): void
    {
        $groups = [
            'Stripe' => ['STRIPE_ACTIVE', 'STRIPE_KEY', 'STRIPE_SECRET'],
            'Wire transfer' => ['WIRE-TRANSFER_ACTIVE', 'Titular cont', 'Denumire Banca', 'Cod IBAN', 'Cod SWIFT'],
            'CCBill' => ['CCBILL_ACTIVE', 'CCBILL_ACC', 'CCBILL_ACC_PACK', 'CCBILL_ACC_CREDITS', 'CCBILL_PACK_HASH', 'CCBILL_CREDITS_HASH'],
            'PayPal' => ['PAYPAL_ACTIVE', 'PAYPAL_ID', 'PAYPAL_SECRET'],
            'CentralPay' => ['CENTRALPAY_ACTIVE', 'CENTRALPAY_CLIENT_ID', 'CENTRALPAY_PUBLICKEY', 'CENTRALPAY_SECRET'],
        ];

        foreach ($groups as $category => $names) {
            DB::table('settings')->whereIn('name', $names)->update(['category' => $category]);
        }
    }

    public function down(): void
    {
        $names = [
            'STRIPE_ACTIVE', 'STRIPE_KEY', 'STRIPE_SECRET',
            'WIRE-TRANSFER_ACTIVE', 'Titular cont', 'Denumire Banca', 'Cod IBAN', 'Cod SWIFT',
            'CCBILL_ACTIVE', 'CCBILL_ACC', 'CCBILL_ACC_PACK', 'CCBILL_ACC_CREDITS', 'CCBILL_PACK_HASH', 'CCBILL_CREDITS_HASH',
            'PAYPAL_ACTIVE', 'PAYPAL_ID', 'PAYPAL_SECRET',
            'CENTRALPAY_ACTIVE', 'CENTRALPAY_CLIENT_ID', 'CENTRALPAY_PUBLICKEY', 'CENTRALPAY_SECRET',
        ];

        DB::table('settings')->whereIn('name', $names)->update(['category' => null]);
    }
};
