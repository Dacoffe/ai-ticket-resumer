<?php

namespace App\Services;

use App\Models\Ticket;

class TicketSimilaritySearch
{
    /**
     * Devolve os N tickets mais semelhantes a $ticket, com o respetivo score.
     *
     * @return array<int, array{ticket: Ticket, score: float}>
     */
    public function topSimilar(Ticket $ticket, int $limit = 5): array
    {
        if (! $ticket->embedding) {
            return [];
        }

        return Ticket::query()
            ->whereNotNull('embedding')
            ->where('id', '!=', $ticket->id)
            ->get(['id', 'subject', 'body', 'category', 'priority', 'summary', 'embedding'])
            ->map(fn (Ticket $candidate) => [
                'ticket' => $candidate,
                'score'  => EmbeddingService::cosineSimilarity($ticket->embedding, $candidate->embedding),
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }
}
