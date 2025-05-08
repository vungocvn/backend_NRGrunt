<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->default(1);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

