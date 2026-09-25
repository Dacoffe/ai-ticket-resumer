<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        Ticket::query()->delete();

        // 3 tickets por cluster garante repetições (o mesmo problema, escrito de
        // forma diferente) sem esgotar as variantes escritas na factory.
        foreach ([
            'billingDuplicateCharge',
            'billingRefundPending',
            'technicalCrashOnReport',
            'technicalSiteTimeout',
            'accountPasswordReset',
            'featureExportData',
        ] as $cluster) {
            Ticket::factory()->count(3)->{$cluster}()->create();
        }

        // Alguns tickets avulsos, sem par semelhante à espera — para se ver que a
        // pesquisa por embeddings também sabe dizer "não há nada parecido".
        Ticket::factory()->count(4)->create();
    }
}
