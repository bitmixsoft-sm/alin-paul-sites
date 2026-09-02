<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index()->nullable();
            $table->bigInteger('pack_id')->index()->nullable();
            $table->integer('price')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('ip_address', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_attempts');
    }
}
