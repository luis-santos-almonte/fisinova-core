<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ProceduresSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/procedures_clean.csv');

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $batch = [];

        foreach ($records as $record) {
            $batch[] = [
                'description' => $record['description'],
                'category' => $record['category'],
                'standard' => $record['standard'],
                'active' => $record['active'],
            ];
        }

        DB::table('procedure_standards')->truncate();
        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('procedure_standards')->insert($chunk);
        }

        $this->command->info('✅ Procedures imported successfully!');
    }
}
