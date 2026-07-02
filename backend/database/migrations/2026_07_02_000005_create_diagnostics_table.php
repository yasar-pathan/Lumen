<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->unique()->constrained('messages')->onDelete('cascade');
            $table->float('retrieval_relevance_avg');
            $table->float('groundedness_score');
            $table->string('root_cause'); // healthy | knowledge_gap | hallucination | ambiguous_query | prompt_instruction_issue
            $table->text('suggested_fix')->nullable();
            $table->jsonb('missing_terms')->nullable();
            $table->integer('latency_ms');
            $table->boolean('safety_flag')->default(false);
            $table->string('provider_name'); // mock | openrouter
            $table->timestamps();

            // Indexes
            $table->index('message_id');
            $table->index('root_cause');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostics');
    }
};
