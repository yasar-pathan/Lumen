<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_version_id')->constrained('prompt_versions');
            $table->string('title')->nullable();
            $table->timestamps();

            // Index for foreign key
            $table->index('prompt_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
