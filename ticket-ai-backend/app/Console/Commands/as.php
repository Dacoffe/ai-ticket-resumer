<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\EmbeddingService;
use App\Services\TicketAnalyzer;
use Illuminate\Console\Command;
use Throwable;

class AnalyzeTickets extends Command
{
    /**
     * A factory/seeder só cria subject+body de propósito — este comando é que
     * chama o LLM, separado no tempo, para não estourar o limite de pedidos por
     * minuto dos tiers gratuitos ao semear vários tickets de uma vez.
     */
    protected $signature = 'tickets:analyze-pending
        {--limit=100 : Máximo de tickets a processar nesta execução}
        {--sleep=3 : Segundos de espera entre cada ticket}';

    protected $description = 'Classifica e gera embeddings para tickets ainda sem análise';

    public function handle(TicketAnalyzer $analyzer, EmbeddingService $embedder): int
    {
        $tickets = Ticket::query()
            ->whereNull('analyzed_at')
            ->whereNull('analysis_error')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('Não há tickets pendentes.');

            return self::SUCCESS;
        }

        $this->info("A processar {$tickets->count()} ticket(s)...");
        $bar = $this->output->createProgressBar($tickets->count());

        foreach ($tickets as $ticket) {
            try {
                $result = $analyzer->analyze($ticket->subject, $ticket->body);
                $ticket->update($result + ['analyzed_at' => now(), 'analysis_error' => null]);

                $vector = $embedder->embed($ticket->subject . "\n" . $ticket->body);
                $ticket->update(['embedding' => $vector]);
            } catch (Throwable $e) {
                $ticket->update(['analysis_error' => mb_substr($e->getMessage(), 0, 500)]);
                $this->newLine();
                $this->warn("Ticket #{$ticket->id} falhou: {$e->getMessage()}");
            }

            $bar->advance();
            sleep((int) $this->option('sleep'));
        }

        $bar->finish();
        $this->newLine();

        return self::SUCCESS;
    }
}
