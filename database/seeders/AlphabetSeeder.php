<?php

namespace Database\Seeders;

use App\Models\Alphabet;
use App\Models\AlphabetGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AlphabetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->truncateTables();

            $jsonPath = database_path('data/alphabets.json');
            if (!File::exists($jsonPath)) {
                $this->command->error("JSON data file not found at: {$jsonPath}");
                return;
            }

            $jsonData = File::get($jsonPath);
            $groups = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Failed to parse JSON file: " . json_last_error_msg());
                return;
            }

            foreach ($groups as $groupData) {
                $group = AlphabetGroup::create([
                    'name' => $groupData['name'],
                    'type' => $groupData['type'],
                    'order_index' => $groupData['order_index'],
                ]);

                foreach ($groupData['alphabets'] as $alphabetData) {
                    Alphabet::create([
                        'group_id' => $group->id,
                        'character' => $alphabetData['character'],
                        'romaji' => $alphabetData['romaji'],
                        'stroke_path' => $alphabetData['stroke_path'] ?? null,
                    ]);
                }
            }
        });

        $this->command->info('Alphabet groups and characters seeded successfully.');
    }

    /**
     * Truncate the tables safely.
     */
    protected function truncateTables(): void
    {
        Alphabet::query()->delete();
        AlphabetGroup::query()->delete();
    }
}
