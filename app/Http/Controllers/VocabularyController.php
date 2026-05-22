<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


class VocabularyController extends Controller
{
    /**
     * Display a listing of vocabularies, with optional search, filtering, and pagination.
     */
    #[OA\Get(path: "/vocabularies", summary: "List Vocabularies", tags: ["Vocabularies"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "group_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "paginate", in: "query", schema: new OA\Schema(type: "string", default: "true")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]

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
     #[OA\Get(path: "/vocabularies/{id}", summary: "Vocabulary Details", tags: ["Vocabularies"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    
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

    /**
     Get a list ramdom vocabularies of flashcards
     */
     #[OA\Get(path: "/vocabularies/random", summary: "Random Flashcards", tags: ["Vocabularies"],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "group_id", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Vocabulary"))
            ]))
        ]
    )]
    public function random(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string',
            'group_id' => 'nullable|integer|exists:alphabet_groups,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Vocabulary::query();

        //loc theo chu de
        if($request->filled('type')){
            $query->where('type',$request->query('type'));
        }
        //loc theo nhom chu cai
        if($request->filled('group_id')){
            $query->where('group_id',$request->query('group_id'));
        }
        //limit 
        $limit =(int) $request->query('limit',10);

        $vocabularies = $query->inRandomOrder()->limit($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Random vocabularies retrieved successfully',
            'data' => $vocabularies
        ]);
    }
} 
