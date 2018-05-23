<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As an administrator I want to be able to filter users by name (partial match), type (normal or administrator) and
 * blocked status (whether it is blocked or not).
 */
class UserStory06Test extends UserStoryTestCase
{

    private function seedTestUsers()
    {
        $this->seedAdminUser();
        $this->seedUser('aaa', 'user1@mail.pt');
        $this->seedUser('aab', 'user2@mail.pt', true);
        $this->seedUser('bbc', 'user3@mail.pt', false, true);
        $this->seedUser('abbd', 'user4@mail.pt', true, true);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_empty_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?name=')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_full_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->actingAs($this->adminUser)
            ->get('/users?name=aaa')
            ->assertStatus(200)
            ->assertSee('aaa')
            ->assertSeeCount('@mail.pt', 1, 'Expects only the user user1@mail.pt');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_partial_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->actingAs($this->adminUser)
            ->get('/users?name=b')
            ->assertStatus(200)
            ->assertSee('aab')
            ->assertSee('aab')
            ->assertSee('bbd')
            ->assertSeeCount('@mail.pt', 3, 'Expects only 3 users');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_empty_type_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?type=')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_ignores_invalid_type_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?type=asda')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_admin_type_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?type=admin')
            ->assertStatus(200)
            ->assertSee('rootiam')
            ->assertSee('aab')
            ->assertSee('bbd')
            ->assertSeeCount('@mail.pt', 3, 'Expects only 3 users');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_normal_type_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?type=normal')
            ->assertStatus(200)
            ->assertSee('aaa')
            ->assertSee('bbc')
            ->assertSeeCount('@mail.pt', 2, 'Expects only 2 users');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_empty_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_ignores_invalid_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=asda')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
            $this->response->assertSee($user->email);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_blocked_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=blocked')
            ->assertStatus(200)
            ->assertSee('bbc')
            ->assertSee('bbd')
            ->assertSeeCount('@mail.pt', 2, 'Expects only 2 users');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_unblocked_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=unblocked')
            ->assertStatus(200)
            ->assertSee('rootiam')
            ->assertSee('aaa')
            ->assertSee('aab')
            ->assertSeeCount('@mail.pt', 3, 'Expects only 3 users');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_name_and_type_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?type=admin&name=aa')
            ->assertStatus(200)
            ->assertSee('aab')
            ->assertSeeCount('@mail.pt', 1, 'Expects only 1 user');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_name_and_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=blocked&name=ab')
            ->assertStatus(200)
            ->assertSee('abbd')
            ->assertSeeCount('@mail.pt', 1, 'Expects only 1 user');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function users_index_supports_name_and_type_and_status_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/users?status=blocked&name=bd&type=normal')
            ->assertStatus(200)
            ->assertSeeCount('@mail.pt', 0, 'Expects no users');
    }
}
