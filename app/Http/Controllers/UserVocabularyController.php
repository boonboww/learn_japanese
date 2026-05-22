<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class UserVocabularyController extends Controller
{
    // API 1: Thả tim / Bỏ thả tim từ vựng (Toggle Bookmark)
    #[OA\Post(path: "/auth/vocabularies/{id}/bookmark", summary: "Toggle Bookmark", security: [["bearerAuth" => []]], tags: ["User Progress"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]

    public function toggleBookmark(int $id): JsonResponse
    {
        $user = Auth::user(); // Lấy user hiện tại đang đăng nhập
        assert($user instanceof \App\Models\User);
        Vocabulary::findOrFail($id);

        // Sử dụng syncWithoutDetaching để tránh ghi đè dữ liệu cũ, chỉ cập nhật cột pivot
        $pivot = $user->vocabularies()->where('vocabulary_id', $id)->first()?->pivot;

        $newBookmarkState = $pivot ? !$pivot->is_bookmarked : true;

        $user->vocabularies()->syncWithoutDetaching([
            $id => ['is_bookmarked' => $newBookmarkState]
        ]);

        return response()->json([
            'success' => true,
            'message' => $newBookmarkState ? 'Đã lưu vào danh sách yêu thích' : 'Đã xóa khỏi danh sách yêu thích',
            'is_bookmarked' => $newBookmarkState
        ]);
    }

    // API 2: Cập nhật trạng thái học (Learning Status)
    #[OA\Post(path: "/auth/vocabularies/{id}/status", summary: "Update Learn Status", security: [["bearerAuth" => []]], tags: ["User Progress"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "status", type: "string", enum: ["learning", "mastered", "not_learned"], example: "mastered")
        ])),
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:learning,mastered,not_learned'
        ]);

        $user = Auth::user();
        assert($user instanceof \App\Models\User);
        $status = $request->input('status');
        Vocabulary::findOrFail($id);

        if ($status === 'not_learned') {
            // Nếu chuyển trạng thái về "Chưa học", chúng ta có thể kiểm tra xem nếu không bookmark thì xóa hẳn dòng pivot để tiết kiệm bộ nhớ
            $pivot = $user->vocabularies()->where('vocabulary_id', $id)->first()?->pivot;
            if ($pivot && !$pivot->is_bookmarked) {
                $user->vocabularies()->detach($id);
            } else {
                $user->vocabularies()->syncWithoutDetaching([
                    $id => ['status' => 'not_learned'] // Giữ nguyên cột bookmark nhưng cập nhật trạng thái thành not_learned
                ]);
            }
        } else {
            // Cập nhật trạng thái learning hoặc mastered
            $user->vocabularies()->syncWithoutDetaching([
                $id => ['status' => $status]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái học thành công',
            'status' => $status
        ]);
    }
}
