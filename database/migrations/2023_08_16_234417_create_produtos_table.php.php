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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uf_id'); 
            $table->unsignedBigInteger('user_id'); 
            $table->string('nome'); 
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->foreign('uf_id')->references('id')->on('ufs'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
