<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $endpoint =
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

 public function sendPrompt(string $prompt): string
{
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post(
        $this->endpoint . '?key=' . config('services.google_ai.key'),
        [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ]
    );

    if ($response->failed()) {
        Log::error('Gemini API failed', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return 'AI service is currently unavailable.';
    }

    return trim(
        $response->json('candidates.0.content.parts.0.text')
        ?? 'No response from AI.'
    );
}

}