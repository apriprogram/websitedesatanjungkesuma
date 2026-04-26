<?php

namespace Database\Seeders;

use App\Models\Agenda;
use Illuminate\Database\Seeder;

class AgendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 15 Active Agendas
        Agenda::factory(15)->create([
            'is_completed' => false,
        ]);

        // Generate 15 Completed Agendas (History)
        Agenda::factory(15)->create([
            'is_completed' => true,
        ]);
    }
}
