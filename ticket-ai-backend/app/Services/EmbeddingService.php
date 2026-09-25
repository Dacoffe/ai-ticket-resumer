<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingService
{
    /**
     * @return float[]
     */
    public function embed(string $text): array
    {
        $cfg = config('llm.embeddings');

        if (empty($cfg['api_key'])) {
            throw new RuntimeException('Embeddings não configurados: falta GEMINI_API_KEY no .env');
        }

        // Endpoint nativo da Gemini (embedContent), não o compatível com OpenAI:
        // a key vai na query string, não como Bearer token.
        $response = Http::baseUrl($cfg['base_url'])
            ->timeout(30)
            ->retry(2, 1000, throw: false)
            ->post("/models/{$cfg['model']}:embedContent?key={$cfg['api_key']}", [
                'content' => [
                    'parts' => [['text' => $text]],
                ],
                'outputDimensionality' => $cfg['output_dimensions'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Erro ao gerar embedding ({$response->status()}): " . $response->body());
        }

        $values = $response->json('embedding.values');

        if (! is_array($values) || $values === []) {
            throw new RuntimeException('Resposta de embedding vazia ou inválida');
        }

        return $values;
    }

    public static function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * ($b[$i] ?? 0);
            $normA += $value ** 2;
            $normB += ($b[$i] ?? 0) ** 2;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
