<?php

namespace Tests\Feature;

use App\Models\AlphabetGroup;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserVocabularyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Vocabulary $vocabulary;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Get JWT token
        $this->token = auth()->tokenById($this->user->id);

        // Create an alphabet group and a vocabulary item
        $group = AlphabetGroup::create([
            'name' => 'Seion',
            'type' => 'hiragana',
            'order_index' => 1,
        ]);

        $this->vocabulary = Vocabulary::create([
            'group_id' => $group->id,
            'word' => 'こんにちは',
            'romaji' => 'konnichiwa',
            'meaning' => 'Hello',
        ]);
    }

    /**
     * Test unauthorized requests fail.
     */
    public function test_unauthorized_user_cannot_access_endpoints(): void
    {
        $this->postJson("/api/vocabularies/{$this->vocabulary->id}/bookmark")
            ->assertStatus(401);

        $this->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'learning'])
            ->assertStatus(401);
    }

    /**
     * Test bookmark toggle functionality.
     */
    public function test_user_can_toggle_bookmark(): void
    {
        // First request to bookmark
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/bookmark");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã lưu vào danh sách yêu thích',
                'is_bookmarked' => true,
            ]);

        $this->assertTrue(
            (bool) $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
                ->pivot->is_bookmarked
        );

        // Second request to un-bookmark
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/bookmark");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'is_bookmarked' => false,
            ]);

        $this->assertFalse(
            (bool) $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
                ->pivot->is_bookmarked
        );
    }

    /**
     * Test updating learning status.
     */
    public function test_user_can_update_learning_status(): void
    {
        // Update to learning
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'learning']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'learning',
            ]);

        $this->assertEquals(
            'learning',
            $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
                ->pivot->status
        );

        // Update to mastered
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'mastered']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'mastered',
            ]);

        $this->assertEquals(
            'mastered',
            $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
                ->pivot->status
        );
    }

    /**
     * Test status update to not_learned detaches record if not bookmarked.
     */
    public function test_updating_status_to_not_learned_detaches_if_not_bookmarked(): void
    {
        // Set status to learning first (creates pivot row)
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'learning']);

        $this->assertNotNull(
            $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
        );

        // Now set status to not_learned
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'not_learned']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'not_learned',
            ]);

        // Row should be detached (deleted) because is_bookmarked is false by default
        $this->assertNull(
            $this->user->vocabularies()
                ->where('vocabulary_id', $this->vocabulary->id)
                ->first()
        );
    }

    /**
     * Test status update to not_learned updates status but keeps row if bookmarked.
     */
    public function test_updating_status_to_not_learned_keeps_row_if_bookmarked(): void
    {
        // Bookmark first (creates pivot row with is_bookmarked = true)
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/bookmark");

        // Set status to mastered
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'mastered']);

        // Now set status to not_learned
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/vocabularies/{$this->vocabulary->id}/status", ['status' => 'not_learned']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'not_learned',
            ]);

        // Row should still exist with is_bookmarked = true and status = not_learned
        $pivot = $this->user->vocabularies()
            ->where('vocabulary_id', $this->vocabulary->id)
            ->first()
            ->pivot;

        $this->assertTrue((bool)$pivot->is_bookmarked);
        $this->assertEquals('not_learned', $pivot->status);
    }
}
