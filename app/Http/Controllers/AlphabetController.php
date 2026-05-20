<?php

namespace App\Http\Controllers;

use App\Models\AlphabetGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlphabetController extends Controller
{
    /**
     * Display a listing of the alphabet groups and their characters.
     */
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
