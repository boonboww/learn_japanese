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
        Schema::create('user_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vocabulary_id')->constrained('vocabularies')->onDelete('cascade');
            
            // Bookmark/Favorite status
            $table->boolean('is_bookmarked')->default(false);
            
            // Learning status: learning ( đang học ), mastered ( đã thuộc )
            // Trạng thái mặc định là 'learning'. Nếu không tồn tại dòng dữ liệu thì ngầm định là 'not_learned' ( chưa học ).
            $table->string('status', 20)->default('learning');
            
            $table->timestamps();

            // Đảm bảo mỗi cặp user_id và vocabulary_id chỉ xuất hiện tối đa 1 lần
            $table->unique(['user_id', 'vocabulary_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vocabularies');
    }
};
