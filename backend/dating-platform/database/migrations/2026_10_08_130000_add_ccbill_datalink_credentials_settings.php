<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Client's request (2026-10-08): clicking "Unsubscribe" on /packages only ever cancelled
     * a Stripe subscription (PaymentsController::unsubscribe()) - a CCBill subscriber kept
     * being rebilled by CCBill regardless, since nothing told CCBill to actually stop. CCBill's
     * Subscription Management API (datalink.ccbill.com/utils/subscriptionManagement.cgi) needs
     * separate DataLink credentials (a username/password pair, NOT the Dynamic Pricing salt) -
     * these are account-wide, not per sub-account. Found under the main account's "Data Link"
     * menu (Account Info -> Account Admin -> Data Link -> Data Link Services Suite), which
     * client_support@ccbill.com can also set up/reset if it doesn't already exist.
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_DATALINK_USERNAME'],
            ['value' => '', 'type' => '', 'category' => 'CCBill', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_DATALINK_PASSWORD'],
            ['value' => '', 'type' => '', 'category' => 'CCBill', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['CCBILL_DATALINK_USERNAME', 'CCBILL_DATALINK_PASSWORD'])->delete();
    }
};
