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
        Schema::create('vocabulary_kanji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vocabulary_id')->constrained('vocabularies')->onDelete('cascade');
            $table->foreignId('kanji_id')->constrained('kanjis')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary_kanji');
    }
};
