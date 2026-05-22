<?php

namespace App\Http\Controllers;

use App\Models\AlphabetGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AlphabetController extends Controller
{
    /**
     * Display a listing of the alphabet groups and their characters.
     */

    #[OA\Get(path: "/alphabets", summary: "Get Alphabets", tags: ["Alphabets"],
        parameters: [new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string", enum: ["hiragana", "katakana"]))],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/AlphabetGroup"))
            ]))
        ]
    )]
    
    public function index(Request $request): JsonResponse
    {
        $query = AlphabetGroup::query()->with(['alphabets']);

        // Allow filtering by type: 'hiragana' or 'katakana'
        if ($request->has('type')) {
            $type = $request->query('type');
            if (in_array($type, ['hiragana', 'katakana'])) {
                $query->where('type', $type);
            }
        }

        $groups = $query->orderBy('order_index')->get();

        return response()->json([
            'success' => true,
            'message' => 'Alphabet groups retrieved successfully',
            'data' => $groups
        ]);
    }
}
