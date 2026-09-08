<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NULL = not currently boosted. Set to now()+N minutes on purchase
            // (BoostController@activate); FindFriendsController orders boosted_until > now()
            // rows first while it's still in the future, no separate "is boosted" flag needed.
            $table->timestamp('boosted_until')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('boosted_until');
        });
    }
};
