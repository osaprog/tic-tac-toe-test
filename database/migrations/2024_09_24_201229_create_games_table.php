<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::dropIfExists('games');

    Schema::create('games', function (Blueprint $table) {
        $table->bigInteger('id')->unsigned()->primary(); 
        $table->json('board')->default(json_encode(array_fill(0, 9, null))); // 3x3 board
        $table->string('player_x'); // player X
        $table->string('player_o'); // player O
        $table->string('turn'); // current turn
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
