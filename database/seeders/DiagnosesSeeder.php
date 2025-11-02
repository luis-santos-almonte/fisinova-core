<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diagnosis; // usa tu modelo correcto
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class DiagnosesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/cie10_clean_final.csv');

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $batch = [];

        foreach ($records as $record) {
            $batch[] = [
                'code' => $record['code'],
                'description' => $record['description'],
                'category' => $record['category'],
                'standard' => $record['standard'],
                'active' => $record['active'],
            ];
        }

        // Insertar en lotes para eficiencia
        DB::table('diagnostic_standards')->truncate();
        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('diagnostic_standards')->insert($chunk);
        }

        $this->command->info('✅ Diagnoses imported successfully!');
    }
}
