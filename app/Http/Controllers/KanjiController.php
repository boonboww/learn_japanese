<?php

namespace App\Http\Controllers;

use App\Models\Kanji;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class KanjiController extends Controller
{
    /**
     * Display a listing of Kanji characters, with optional search and pagination.
     */
    #[OA\Get(path: "/kanjis", summary: "List Kanjis", tags: ["Kanjis"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "paginate", in: "query", schema: new OA\Schema(type: "string", default: "true")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Kanji::query();

        // 1. Search (OR condition across character, meaning)
        if ($request->has('search')) {
            $search = $request->query('search');
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('character', 'like', '%' . $search . '%')
                      ->orWhere('meaning', 'like', '%' . $search . '%');
                });
            }
        }

        // 2. Pagination / Retrieval
        if ($request->query('paginate') === 'false') {
            $kanjis = $query->orderBy('id')->get();
        } else {
            $perPage = (int) $request->query('per_page', 20);
            if ($perPage < 1) $perPage = 20;
            if ($perPage > 100) $perPage = 100;
            
            $kanjis = $query->orderBy('id')->paginate($perPage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kanjis retrieved successfully',
            'data' => $kanjis
        ]);
    }
 
    /**
     * Display the specified Kanji character details with related vocabulary words.
     */
    #[OA\Get(path: "/kanjis/{id}", summary: "Kanji Details", tags: ["Kanjis"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $kanji = Kanji::with(['vocabularies'])->find($id);

        if (!$kanji) {
            return response()->json([
                'success' => false,
                'message' => 'Kanji not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kanji details retrieved successfully',
            'data' => $kanji
        ]);
    }
}
