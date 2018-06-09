<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As an administrator I want to view a list of registered users (including blocked users and administrators) of
 * the application. For each user show at least the name, email, type and status (blocked or not) fields.
 */
class UserStory05Test extends UserStoryTestCase
{

    private function seedTestUsers()
    {
        $this->seedAdminUser();
        $this->seedUser('user1', 'user1@mail.pt');
        $this->seedUser('user2', 'user2@mail.pt', true);
        $this->seedUser('user3', 'user3@mail.pt', false, true);
        $this->seedUser('user4', 'user4@mail.pt', true, true);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_shows_myself()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->get('/users')
            ->assertStatus(200)
            ->assertSeeText('rootiam')
            ->assertSeeText('iamroot@mail.pt');
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_shows_all_registered_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users')
            ->assertStatus(200)
            ->assertPatternCount(
                '/user\-is\-blocked/u',
                2,
                'blocked users count mismatch. Ensure that the class user-is-blocked is applied to a blocked user.'
            )
            ->assertPatternCount(
                '/user\-is\-admin/u',
                3,
                'admin users count mismatch. Ensure that the user-is-admin is applied to an admin user.'
            );

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_is_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        //  total of registered users, total number of accounts and movements registered on the platform
        $this->seedTestUsers();

        $this->get('/users')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_is_not_available_to_regular_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        //  total of registered users, total number of accounts and movements registered on the platform
        $this->seedTestUsers();
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->get('/users')
            ->assertForbidden();
    }
}
