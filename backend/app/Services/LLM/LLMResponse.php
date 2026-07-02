<?php

namespace App\Services\LLM;

class LLMResponse
{
    public function __construct(
        public readonly string $content,
        public readonly int $latencyMs,
        public readonly string $providerName
    ) {}
}
