<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class QuizController extends Controller
{
    /**
     * Generate a dynamic multiple-choice quiz based on vocabulary.
     */
    #[OA\Get(path: "/quizzes/dynamic", summary: "Generate Dynamic Quiz", tags: ["Quizzes"],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "type", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "group_id", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function generateDynamicQuiz(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string',
            'group_id' => 'nullable|integer|exists:alphabet_groups,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = Vocabulary::query();

        // 1. Áp dụng bộ lọc (nếu có)
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->query('group_id'));
        }

        // 2. Lấy ra danh sách các từ vựng đích để đặt câu hỏi (mặc định 10 từ)
        $limit = (int) $request->query('limit', 10);
        $targetVocabs = $query->inRandomOrder()->limit($limit)->get();

        if ($targetVocabs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy từ vựng nào phù hợp với bộ lọc.',
                'data' => []
            ], 404);
        }

        // 3. Tạo một pool (kho từ vựng phụ) để sinh đáp án nhiễu (distractors)
        // Lấy khoảng 50 từ ngẫu nhiên để tránh truy vấn cơ sở dữ liệu nhiều lần trong vòng lặp
        $poolQuery = Vocabulary::query();
        if ($request->filled('type')) {
            $poolQuery->where('type', $request->query('type'));
        }
        $pool = $poolQuery->inRandomOrder()->limit(50)->get();

        // Nếu pool quá nhỏ, lấy thêm từ kho từ vựng chung
        if ($pool->count() < 10) {
            $pool = Vocabulary::inRandomOrder()->limit(50)->get();
        }

        $questions = [];

        foreach ($targetVocabs as $vocab) {
            // Chọn ngẫu nhiên loại câu hỏi: Hỏi nghĩa (meaning) hoặc Hỏi cách đọc (furigana)
            $questionType = 'meaning';
            if ($vocab->furigana && rand(0, 1) === 1) {
                $questionType = 'furigana';
            }

            $correctAnswer = $questionType === 'meaning' ? $vocab->meaning : $vocab->furigana;

            // Tìm 3 đáp án nhiễu từ pool từ vựng đã lấy trước đó
            $distractorValues = [];
            foreach ($pool as $p) {
                if ($p->id === $vocab->id) {
                    continue;
                }

                $val = $questionType === 'meaning' ? $p->meaning : $p->furigana;

                // Kiểm tra điều kiện đáp án nhiễu hợp lệ (không rỗng, không trùng đáp án đúng/nhiễu khác)
                if ($val && $val !== $correctAnswer && !in_array($val, $distractorValues)) {
                    $distractorValues[] = $val;
                }

                if (count($distractorValues) === 3) {
                    break;
                }
            }

            // Trường hợp hy hữu không đủ 3 đáp án nhiễu từ pool, lấy thêm từ vựng bất kỳ
            if (count($distractorValues) < 3) {
                $fallbackPool = Vocabulary::where('id', '!=', $vocab->id)
                    ->inRandomOrder()
                    ->limit(20)
                    ->get();

                foreach ($fallbackPool as $p) {
                    $val = $questionType === 'meaning' ? $p->meaning : $p->furigana;
                    if ($val && $val !== $correctAnswer && !in_array($val, $distractorValues)) {
                        $distractorValues[] = $val;
                    }
                    if (count($distractorValues) === 3) {
                        break;
                    }
                }
            }

            // Gộp đáp án đúng với đáp án nhiễu và xáo trộn vị trí
            $options = array_merge([$correctAnswer], $distractorValues);
            shuffle($options);

            // Xây dựng tiêu đề câu hỏi tiếng Việt
            $questionText = $questionType === 'meaning'
                ? "Từ \"{$vocab->word}\" có nghĩa là gì?"
                : "Từ \"{$vocab->word}\" có cách đọc Furigana là gì?";

            $questions[] = [
                'vocabulary_id' => $vocab->id,
                'word' => $vocab->word,
                'question_text' => $questionText,
                'question_type' => $questionType,
                'options' => $options,
                'correct_answer' => $correctAnswer,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Dynamic quiz generated successfully',
            'data' => $questions
        ]);
    }
}
