<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Storage;
use Tests\TestCase;

/**
 * As a user I want to view the list of users that belong to my group of associate members.
 * The list should show at least the name and email of the member.
 */
class UserStory12Test extends UserStoryTestCase
{
    private function seedTestUsers()
    {
        $this->seedAdminUser();
        $this->seedMainUser();

        $this->seedUser('2d9e08d0', 'user1@mail.pt');
        $this->seedUser('836d2620', 'user2@mail.pt', true);
        $this->seedUser('a90eb144', 'user3@mail.pt', false, true);
        $this->seedUser('b46c85c9', 'user4@mail.pt', true, true);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_is_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->get('/me/associates')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_is_empty_if_user_has_no_associates()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->mainUser)
            ->get('/me/associates')
            ->assertStatus(200)
            ->assertDontSeeAll([
                '2d9e08d0', '836d2620', 'a90eb144', 'b46c85c9', 'rootiam',
                'user1@mail.pt', 'user2@mail.pt', 'user3@mail.pt', 'user4@mail.pt', 'iamroot@mail.pt'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_is_not_empty()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $this->mainUser->id, 'associated_user_id' => $user1->id],
            ['main_user_id' => $this->mainUser->id, 'associated_user_id' => $user2->id]
        ]);

        $this->actingAs($this->mainUser)
            ->get('/me/associates')
            ->assertStatus(200)
            ->assertSeeAll([
                '2d9e08d0', '836d2620',
                'user1@mail.pt', 'user2@mail.pt'
            ], 'Missing associates')
            ->assertDontSeeAll([
                'a90eb144', 'b46c85c9', 'rootiam',
                'user3@mail.pt', 'user4@mail.pt', 'iamroot@mail.pt'
            ], 'Invalid associates');
    }



    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_works_with_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $this->adminUser->id, 'associated_user_id' => $user1->id],
            ['main_user_id' => $this->adminUser->id, 'associated_user_id' => $user2->id]
        ]);

        $this->actingAs($this->adminUser)
            ->get('/me/associates')
            ->assertStatus(200)
            ->assertSeeAll([
                '2d9e08d0', '836d2620',
                'user1@mail.pt', 'user2@mail.pt'
            ], 'Missing associates')
            ->assertDontSeeAll([
                'a90eb144', 'b46c85c9', 'regular user',
                'user3@mail.pt', 'user4@mail.pt', 'user@mail.pt'
            ], 'Invalid associates');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_does_not_show_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $this->mainUser->id, 'associated_user_id' => $user1->id],
            ['main_user_id' => $user1->id, 'associated_user_id' => $this->mainUser->id]
        ]);

        $this->actingAs($this->mainUser)
            ->get('/me/associates')
            ->assertStatus(200)
            ->assertSeeAll([
                '2d9e08d0',
                'user1@mail.pt',
            ], 'Missing associates')
            ->assertDontSeeAll([
                '836d2620', 'a90eb144', 'b46c85c9', 'rootiam',
                'user2@mail.pt', 'user3@mail.pt', 'user4@mail.pt', 'iamroot@mail.pt'
            ], 'Invalid associates');
    }
}
