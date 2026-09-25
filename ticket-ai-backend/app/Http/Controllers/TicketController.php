<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\EmbeddingService;
use App\Services\TicketAnalyzer;
use App\Services\TicketSimilaritySearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        return Ticket::query()
            ->when($request->query('category'), fn ($q, $v) => $q->where('category', $v))
            ->when($request->query('priority'), fn ($q, $v) => $q->where('priority', $v))
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request, TicketAnalyzer $analyzer, EmbeddingService $embedder): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body'    => ['required', 'string', 'max:10000'],
        ]);

        $ticket = Ticket::create($data);
        $this->runAnalysis($ticket, $analyzer, $embedder);

        return response()->json($ticket->fresh(), 201);
    }

    public function reanalyze(Ticket $ticket, TicketAnalyzer $analyzer, EmbeddingService $embedder): JsonResponse
    {
        $this->runAnalysis($ticket, $analyzer, $embedder);

        return response()->json($ticket->fresh());
    }

    /**
     * Tickets semanticamente semelhantes (RAG): compara o embedding deste ticket
     * com o de todos os outros já indexados e devolve os mais próximos.
     */
    public function similar(Ticket $ticket, TicketSimilaritySearch $search): JsonResponse
    {
        if (! $ticket->embedding) {
            return response()->json([
                'message' => 'Este ticket ainda não tem embedding (falhou a análise ou está pendente).',
                'results' => [],
            ]);
        }

        $matches = collect($search->topSimilar($ticket))->map(fn ($m) => [
            'id'       => $m['ticket']->id,
            'subject'  => $m['ticket']->subject,
            'category' => $m['ticket']->category,
            'priority' => $m['ticket']->priority,
            'summary'  => $m['ticket']->summary,
            'score'    => round($m['score'], 4),
        ]);

        return response()->json(['results' => $matches]);
    }

    private function runAnalysis(Ticket $ticket, TicketAnalyzer $analyzer, EmbeddingService $embedder): void
    {
        try {
            $result = $analyzer->analyze($ticket->subject, $ticket->body);

            $ticket->update($result + [
                'analyzed_at'    => now(),
                'analysis_error' => null,
            ]);
        } catch (Throwable $e) {
            // O ticket fica guardado mesmo que o LLM falhe (rate limit, timeout, etc.)
            report($e);
            $ticket->update(['analysis_error' => mb_substr($e->getMessage(), 0, 500)]);

            return; // sem classificação, não vale a pena gastar quota a gerar embedding
        }

        // O embedding é independente da classificação: se falhar (ex.: rate limit
        // separado do provider de chat), a classificação já feita não se perde.
        try {
            $vector = $embedder->embed($ticket->subject . "\n" . $ticket->body);
            $ticket->update(['embedding' => $vector]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
