<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', static function (Blueprint $table): void {
            $table->longText('summary')->nullable()->after('conversion_context');
            $table->unsignedBigInteger('summarized_through_message_id')->nullable()->after('summary');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', static function (Blueprint $table): void {
            $table->dropColumn(['summary', 'summarized_through_message_id']);
        });
    }
};
