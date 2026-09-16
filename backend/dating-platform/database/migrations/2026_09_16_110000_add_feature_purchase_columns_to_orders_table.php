<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a Pack represent a hidden "paid feature activation" purchase (Boost's real-money
     * option, and whatever similar features the client adds later - Highlighted profile, etc.)
     * instead of only ever a real, browsable package.
     *
     * Deliberately on `packages`, not `orders`: PaymentsController::newpayment() (the single
     * entry point every active payment provider - CentralPay, PayPal, CCBill, Stripe,
     * Wire-transfer - ultimately goes through) always creates its OWN new Order row from
     * whatever pack_id it's given; it has no way to accept a pre-built Order. Tagging the
     * *Pack* instead means every one of those payment success paths, which already look the
     * Pack up via $order->package_id right after a payment succeeds, can check
     * $pack->feature_key with no changes needed to how Orders themselves get created.
     *
     * A hidden feature-pack is created fresh per purchase (BoostController::checkout(), mirroring
     * PaymentsController::createCustomPack()'s existing "make a one-off custom Pack" pattern) -
     * custom = 1 so it never appears on the public /packages listing (Pack::where('custom', '!=', 1)),
     * credits = 0 and type = 'credits' so the normal payment-success grant logic is a harmless
     * no-op instead of actually assigning it to the user as their active package.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('packages', 'feature_key')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->string('feature_key')->nullable()->after('custom');
                $table->integer('feature_duration_minutes')->nullable()->after('feature_key');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('packages', 'feature_key')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn(['feature_key', 'feature_duration_minutes']);
            });
        }
    }
};
