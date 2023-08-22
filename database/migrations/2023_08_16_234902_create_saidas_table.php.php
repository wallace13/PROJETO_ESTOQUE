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
        Schema::create('saidas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estoque_id'); 
            $table->unsignedBigInteger('entrada_id'); 
            $table->double('quantidade', 8, 2); 
            $table->timestamps();

            $table->foreign('estoque_id')->references('id')->on('estoques');
            $table->foreign('entrada_id')->references('id')->on('entradas');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saidas');
    }
};
