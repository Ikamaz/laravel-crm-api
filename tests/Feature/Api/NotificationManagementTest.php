<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_their_notifications_with_pagination(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $olderNotification = $this->createNotification($user, 'Older reminder');
        $latestNotification = $this->createNotification($user, 'Latest reminder');
        $this->createNotification($anotherUser, 'Private reminder');

        DB::table('notifications')
            ->where('id', $olderNotification)
            ->update(['created_at' => now()->subMinute()]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $latestNotification)
            ->assertJsonPath('data.0.type', 'task_due_reminder')
            ->assertJsonPath('data.0.payload.title', 'Latest reminder')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertDontSee('Private reminder');
    }

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $notificationId = $this->createNotification($user, 'My reminder');
        $otherNotificationId = $this->createNotification($anotherUser, 'Another reminder');

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.id', $notificationId)
            ->assertJsonPath('data.type', 'task_due_reminder')
            ->assertJsonStructure([
                'data' => ['id', 'type', 'payload', 'read_at', 'created_at', 'updated_at'],
            ]);

        $this->assertNotNull($response->json('data.read_at'));

        $this->assertNotNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at'),
        );
        $this->assertNull(
            DB::table('notifications')->where('id', $otherNotificationId)->value('read_at'),
        );
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $notificationId = $this->createNotification($anotherUser, 'Private reminder');

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/notifications/{$notificationId}/read")
            ->assertNotFound();

        $this->assertNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at'),
        );
    }

    public function test_user_can_mark_all_and_only_their_unread_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $firstUnreadId = $this->createNotification($user, 'First reminder');
        $secondUnreadId = $this->createNotification($user, 'Second reminder');
        $alreadyReadId = $this->createNotification($user, 'Already read reminder');
        $otherUnreadId = $this->createNotification($anotherUser, 'Other user reminder');

        DB::table('notifications')
            ->where('id', $alreadyReadId)
            ->update(['read_at' => now()->subMinute()]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated', 2);

        foreach ([$firstUnreadId, $secondUnreadId, $alreadyReadId] as $notificationId) {
            $this->assertNotNull(
                DB::table('notifications')->where('id', $notificationId)->value('read_at'),
            );
        }

        $this->assertNull(
            DB::table('notifications')->where('id', $otherUnreadId)->value('read_at'),
        );
    }

    public function test_notification_page_size_must_be_between_one_and_one_hundred(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');

        $this->getJson('/api/v1/notifications?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    private function createNotification(User $user, string $title): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\Notifications\TaskDueReminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'type' => 'task_due_reminder',
                'task_id' => 123,
                'customer_id' => 456,
                'title' => $title,
                'due_at' => now()->addHour()->toISOString(),
            ], JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
