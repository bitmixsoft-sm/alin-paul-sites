<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client's request (2026-09-21/22): admin/editor can set a price on an individual photo or
     * on a whole album; a client pays (credits or real money, per the CONTENT_UNLOCK_PRICE_MODE
     * setting - see the next migration) to unlock it, permanently, no expiration.
     *
     * price = 0 (or null) means free/unlocked-for-everyone, on both tables independently - a
     * photo inside a priced album can still be individually free (client's answer #4: "pozele
     * lasate in 0 sa fie vizibile tuturor").
     *
     * blurred_name: a server-generated (GD, no new package - see ImageBlurService) low-detail
     * copy of a priced photo, stored under its own random name in the SAME public images
     * folder as the original. Deliberately not a move to some access-controlled private
     * storage/streaming controller - that would touch the 30+ places across the app that
     * already render `/storage/images/{name}` directly. Instead the real file's name is simply
     * never sent to a browser that hasn't paid (see ImageGet::displayName()) - a soft paywall,
     * the same trust model this app already uses for its video files, not DRM-grade protection.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('images', 'price')) {
            Schema::table('images', function (Blueprint $table) {
                $table->decimal('price', 8, 2)->nullable()->default(0)->after('description');
                $table->string('blurred_name')->nullable()->after('price');
            });
        }

        if (! Schema::hasColumn('albums', 'price')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->decimal('price', 8, 2)->nullable()->default(0)->after('description');
            });
        }

        if (! Schema::hasTable('content_unlocks')) {
            Schema::create('content_unlocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                // 'image' or 'album' - see App\ContentUnlock::TYPE_IMAGE/TYPE_ALBUM.
                $table->string('unlockable_type', 20);
                $table->unsignedInteger('unlockable_id');
                $table->string('method', 20); // 'credits' or 'money'
                $table->decimal('price_paid', 8, 2);
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['user_id', 'unlockable_type', 'unlockable_id']);
                $table->index(['unlockable_type', 'unlockable_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_unlocks');

        if (Schema::hasColumn('albums', 'price')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        if (Schema::hasColumn('images', 'price')) {
            Schema::table('images', function (Blueprint $table) {
                $table->dropColumn(['price', 'blurred_name']);
            });
        }
    }
};
