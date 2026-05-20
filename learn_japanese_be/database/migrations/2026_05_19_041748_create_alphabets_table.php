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
        Schema::create('alphabets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('alphabet_groups')->onDelete('cascade');
            $table->string('character', 10);
            $table->string('romaji', 10);
            $table->text('stroke_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alphabets');
    }
};
