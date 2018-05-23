<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As an administrator I want to be able to block or reactivate users or to change its type to normal or administrator.
 * These changes can be applied to any user, except myself.
 */
class UserStory07Test extends UserStoryTestCase
{

    private function seedTestUsers()
    {
        $this->seedAdminUser();
        $this->seedMainUser();
        $this->seedUser('user1', 'user1@mail.pt');
        $this->seedUser('user2', 'user2@mail.pt', true);
        $this->seedUser('user3', 'user3@mail.pt', false, true);
        $this->seedUser('user4', 'user4@mail.pt', true, true);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function block_operation_validates_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/23/block")
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_block_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/block")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user1@mail.pt')->first();
        $this->assertTrue((bool)$user->blocked, 'User is not blocked');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function block_operation_ignores_blocked_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user3@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/block")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user3@mail.pt')->first();
        $this->assertTrue((bool)$user->blocked, 'User is not blocked');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function unblock_operation_validates_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/23/unblock")
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_unblock_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $user = User::where('email', 'user3@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/unblock")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user3@mail.pt')->first();
        $this->assertFalse((bool)$user->blocked, 'User is blocked');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function unblock_operation_ignores_unblocked_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/unblock")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user1@mail.pt')->first();
        $this->assertFalse((bool)$user->blocked, 'User is blocked');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function promote_operation_validates_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/23/promote")
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_promote_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/promote")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user1@mail.pt')->first();
        $this->assertTrue((bool)$user->admin, 'User is not admin');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function promote_operation_ignores_promoted_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user4@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/promote")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user4@mail.pt')->first();
        $this->assertTrue((bool)$user->admin, 'User is not admin');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function demote_operation_validates_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/23/demote")
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_demote_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $user = User::where('email', 'user4@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/demote")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user4@mail.pt')->first();
        $this->assertFalse((bool)$user->admin, 'User is admin');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function demote_operation_ignores_regular_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$user->id}/demote")
            ->assertSuccessfulOrRedirect();

        $user = User::where('email', 'user1@mail.pt')->first();
        $this->assertFalse((bool)$user->admin, 'User is admin');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_cannot_demote_himself()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$this->adminUser->id}/demote")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_cannot_block_himself()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$this->adminUser->id}/block")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_cannot_unblock_himself()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch("/users/{$this->adminUser->id}/unblock")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_admins_can_block()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->mainUser)
            ->patch("/users/{$user->id}/block")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_admins_can_unblock()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user3@mail.pt')->first();

        $this->actingAs($this->mainUser)
            ->patch("/users/{$user->id}/unblock")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_admins_can_promote()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user1@mail.pt')->first();

        $this->actingAs($this->mainUser)
            ->patch("/users/{$user->id}/promote")
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_admins_can_demote()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user = User::where('email', 'user4@mail.pt')->first();

        $this->actingAs($this->mainUser)
            ->patch("/users/{$user->id}/demote")
            ->assertForbidden();
    }
}
