<?php

namespace Database\Seeders;

use App\Models\Alphabet;
use App\Models\AlphabetGroup;
use App\Models\Kanji;
use App\Models\Vocabulary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VocabularyKanjiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->truncateTables();

            // 1. Seed Kanji characters
            $kanjiJsonPath = database_path('data/kanjis.json');
            if (!File::exists($kanjiJsonPath)) {
                $this->command->error("Kanji JSON file not found at: {$kanjiJsonPath}");
                return;
            }

            $kanjis = json_decode(File::get($kanjiJsonPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Failed to parse Kanji JSON: " . json_last_error_msg());
                return;
            }

            $this->command->info("Bulk inserting " . count($kanjis) . " Kanji characters...");
            $kanjiDataList = [];
            foreach ($kanjis as $kanjiData) {
                $kanjiDataList[] = [
                    'character' => $kanjiData['character'],
                    'meaning' => $kanjiData['meaning'],
                    'onyomi' => $kanjiData['onyomi'] ?: null,
                    'kunyomi' => $kanjiData['kunyomi'] ?: null,
                    'stroke_path' => $kanjiData['stroke_path'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Kanji::insert($kanjiDataList);

            // Fetch newly created Kanji mapped by character to get database IDs
            $dbKanjis = Kanji::all();
            $kanjiMap = []; // Character => DB ID
            foreach ($dbKanjis as $k) {
                $kanjiMap[$k->character] = $k->id;
            }

            // 2. Fetch all alphabet groups and characters to map vocabularies correctly
            $groups = AlphabetGroup::all();
            if ($groups->isEmpty()) {
                $this->command->error("No alphabet groups found. Make sure to run AlphabetSeeder first!");
                return;
            }
            $defaultGroupId = $groups->first()->id;

            // Load all alphabet characters with their group ids and types
            $alphabets = Alphabet::with('group')->get();
            $hiraganaMap = []; // character => group_id
            $katakanaMap = []; // character => group_id

            foreach ($alphabets as $alphabet) {
                if ($alphabet->group) {
                    if ($alphabet->group->type === 'katakana') {
                        $katakanaMap[$alphabet->character] = $alphabet->group_id;
                    } else {
                        $hiraganaMap[$alphabet->character] = $alphabet->group_id;
                    }
                }
            }

            // 3. Seed Vocabularies
            $vocabJsonPath = database_path('data/vocabularies.json');
            if (!File::exists($vocabJsonPath)) {
                $this->command->error("Vocabulary JSON file not found at: {$vocabJsonPath}");
                return;
            }

            $vocabularies = json_decode(File::get($vocabJsonPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Failed to parse Vocabulary JSON: " . json_last_error_msg());
                return;
            }

            $this->command->info("Bulk inserting " . count($vocabularies) . " Vocabulary items...");
            $vocabDataList = [];
            
            foreach ($vocabularies as $vocabData) {
                $word = $vocabData['word'];
                $furigana = $vocabData['furigana'] ?? '';
                $firstChar = mb_substr($word, 0, 1);

                // Determine group_id
                $groupId = null;

                // If word starts with Katakana
                if (preg_match('/[\x{30A0}-\x{30FF}]/u', $firstChar)) {
                    $groupId = $katakanaMap[$firstChar] ?? null;
                    if (!$groupId) {
                        // Fallback to hiragana mapping if not found in katakana
                        $groupId = $hiraganaMap[$firstChar] ?? null;
                    }
                } else {
                    // Try furigana's first character or word's first character
                    $furiganaFirst = mb_substr($furigana, 0, 1);
                    $groupId = $hiraganaMap[$furiganaFirst] ?? null;
                    if (!$groupId) {
                        $groupId = $hiraganaMap[$firstChar] ?? null;
                    }
                }

                // If still not matched, fallback to default
                if (!$groupId) {
                    $groupId = $defaultGroupId;
                }

                $vocabDataList[] = [
                    'group_id' => $groupId,
                    'word' => $word,
                    'furigana' => $furigana ?: null,
                    'romaji' => $vocabData['romaji'],
                    'meaning' => $vocabData['meaning'],
                    'type' => $vocabData['type'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert vocabularies in chunks of 500
            foreach (array_chunk($vocabDataList, 500) as $chunk) {
                Vocabulary::insert($chunk);
            }

            // Retrieve all vocabularies to map word_furigana to their DB IDs
            $dbVocabs = Vocabulary::all();
            $vocabMap = []; // word_furigana => DB ID
            foreach ($dbVocabs as $v) {
                $vocabMap[$v->word . '_' . ($v->furigana ?? '')] = $v->id;
            }

            // 4. Map vocabulary to Kanjis programmatically in pivot table
            $this->command->info("Mapping vocabularies to Kanjis in pivot table...");
            $pivotData = [];
            foreach ($vocabularies as $vocabData) {
                $word = $vocabData['word'];
                $furigana = $vocabData['furigana'] ?? '';
                
                $vocabKey = $word . '_' . $furigana;
                $vocabId = $vocabMap[$vocabKey] ?? null;
                if (!$vocabId) {
                    continue;
                }

                $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
                $attachedKanjiIds = [];

                foreach ($chars as $char) {
                    if (isset($kanjiMap[$char])) {
                        $kanjiId = $kanjiMap[$char];
                        if (!in_array($kanjiId, $attachedKanjiIds)) {
                            $attachedKanjiIds[] = $kanjiId;
                        }
                    }
                }

                foreach ($attachedKanjiIds as $kanjiId) {
                    $pivotData[] = [
                        'vocabulary_id' => $vocabId,
                        'kanji_id' => $kanjiId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert pivot data in chunks of 500
            foreach (array_chunk($pivotData, 500) as $chunk) {
                DB::table('vocabulary_kanji')->insert($chunk);
            }

            $this->command->info("Seeded " . count($vocabularies) . " vocabularies and " . count($pivotData) . " pivot relationships successfully.");
        });
    }

    /**
     * Truncate the tables.
     */
    protected function truncateTables(): void
    {
        // Disable foreign key checks for Postgres / MySQL to truncate safely
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'pgsql') {
            DB::statement('SET CONSTRAINTS ALL DEFERRED');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        }

        DB::table('vocabulary_kanji')->truncate();
        Vocabulary::query()->delete();
        Kanji::query()->delete();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'pgsql') {
            // Postgres automatically re-enables constraints after transaction commits
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
