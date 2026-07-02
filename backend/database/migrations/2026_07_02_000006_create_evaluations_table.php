<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->string('reviewer_name')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->string('flag')->nullable(); // good | incorrect | hallucination
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
