<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Client's request (2026-10-08): keep the existing classic CCBill integration
     * (bill.ccbill.com/jpost/signup.cgi + Dynamic Pricing salt) working exactly as before, but
     * add the option to switch to CCBill's newer FlexForms system (api.ccbill.com/wap-frontflex/
     * flexforms/<Flex ID>) instead - some CCBill sub-accounts only get Dynamic Pricing enabled
     * on request, and in the meantime the account may be set up for FlexForms instead (see the
     * "FlexForms Systems" tab in the CCBill admin). CCBILL_INTEGRATION_MODE picks which URL
     * PaymentsController::newpayment() builds for the CCBILL branch - same Settings-driven
     * switch pattern as BOOST_PRICE_MODE. Defaults to 'classic' (today's unchanged behavior).
     */
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_INTEGRATION_MODE'],
            ['value' => 'classic', 'type' => 'select|classic~flexforms', 'category' => 'CCBill', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['name' => 'CCBILL_FLEX_ID'],
            ['value' => '', 'type' => '', 'category' => 'CCBill', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['CCBILL_INTEGRATION_MODE', 'CCBILL_FLEX_ID'])->delete();
    }
};
