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
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('alphabet_groups')->onDelete('cascade');
            $table->string('word', 100);
            $table->string('furigana', 100)->nullable();
            $table->string('romaji', 100);
            $table->string('meaning', 255);
            $table->string('audio_url', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
