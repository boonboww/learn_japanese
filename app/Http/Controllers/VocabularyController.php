<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    /**
     * Display a listing of vocabularies, with optional search, filtering, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vocabulary::query();

        // 1. Search (OR condition across word, furigana, romaji, meaning)
        if ($request->has('search')) {
            $search = $request->query('search');
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('word', 'like', '%' . $search . '%')
                        ->orWhere('furigana', 'like', '%' . $search . '%')
                        ->orWhere('romaji', 'like', '%' . $search . '%')
                        ->orWhere('meaning', 'like', '%' . $search . '%');
                });
            }
        }

        // 2. Filter by Type
        if ($request->has('type')) {
            $type = $request->query('type');
            if (!empty($type)) {
                $query->where('type', $type);
            }
        }

        // 3. Filter by Group ID
        if ($request->has('group_id')) {
            $groupId = $request->query('group_id');
            if (!empty($groupId)) {
                $query->where('group_id', $groupId);
            }
        }

        // 4. Eager load group relation
        $query->with(['group']);

        // 5. Pagination / Retrieval
        if ($request->query('paginate') === 'false') {
            $vocabularies = $query->orderBy('word')->get();
        } else {
            $perPage = (int) $request->query('per_page', 20);
            if ($perPage < 1) $perPage = 20;
            if ($perPage > 100) $perPage = 100;

            $vocabularies = $query->orderBy('word')->paginate($perPage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vocabularies retrieved successfully',
            'data' => $vocabularies
        ]);
    }

    /**
     * Display the specified vocabulary item with related group and kanjis.
     */
    public function show(int $id): JsonResponse
    {
        $vocabulary = Vocabulary::with(['group', 'kanjis'])->find($id);

        if (!$vocabulary) {
            return response()->json([
                'success' => false,
                'message' => 'Vocabulary not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vocabulary details retrieved successfully',
            'data' => $vocabulary
        ]);
    }
}
