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
 * As a user I want to view the list of users whose groups of associate members I belong to.
 * The list should show at least the name and email of the member and a link to the accounts page of the
 * member (see US 14);
 */
class UserStory13Test extends UserStoryTestCase
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
    public function associate_of_is_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->get('me/associate-of')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associate_of_is_empty_if_user_has_no_associates()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->mainUser)
            ->get('me/associate-of')
            ->assertStatus(200)
            ->assertDontSeeAll([
                '2d9e08d0', '836d2620', 'a90eb144', 'b46c85c9', 'rootiam',
                'user1@mail.pt', 'user2@mail.pt', 'user3@mail.pt', 'user4@mail.pt', 'iamroot@mail.pt'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associate_of_is_not_empty()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['associated_user_id' => $this->mainUser->id, 'main_user_id' => $user1->id],
            ['associated_user_id' => $this->mainUser->id, 'main_user_id' => $user2->id]
        ]);

        $this->actingAs($this->mainUser)
            ->get('me/associate-of')
            ->assertStatus(200)
            ->assertSeeAll([
                '2d9e08d0', '836d2620',
                'user1@mail.pt', 'user2@mail.pt'
            ], 'Missing associates-of')
            ->assertDontSeeAll([
                'a90eb144', 'b46c85c9', 'rootiam',
                'user3@mail.pt', 'user4@mail.pt', 'iamroot@mail.pt'
            ], 'Invalid associates-of');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associate_of_works_with_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['associated_user_id' => $this->adminUser->id, 'main_user_id' => $user1->id],
            ['associated_user_id' => $this->adminUser->id, 'main_user_id' => $user2->id]
        ]);

        $this->actingAs($this->adminUser)
            ->get('me/associate-of')
            ->assertStatus(200)
            ->assertSeeAll([
                '2d9e08d0', '836d2620',
                'user1@mail.pt', 'user2@mail.pt'
            ], 'Missing associates of')
            ->assertDontSeeAll([
                'a90eb144', 'b46c85c9', 'regular user',
                'user3@mail.pt', 'user4@mail.pt', 'user@mail.pt'
            ], 'Invalid associates of');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associate_of_does_not_show_associates()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $user1->id, 'associated_user_id' => $this->mainUser->id],
            ['main_user_id' => $this->mainUser->id, 'associated_user_id' => $user2->id]
        ]);

        $this->actingAs($this->mainUser)
            ->get('me/associate-of')
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

    // @codingStandardsIgnoreStart
    /** @test */
    public function associate_of_show_accounts_link()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        $links = User::whereNotIn('id', [$user1->id, $user2->id, $this->mainUser->id])
            ->pluck('id')
            ->map(function ($id) {
                return 'href="'.url('/accounts/'.$id).'"';
            });
        DB::table('associate_members')->insert([
            ['associated_user_id' => $this->mainUser->id, 'main_user_id' => $user1->id],
            ['associated_user_id' => $this->mainUser->id, 'main_user_id' => $user2->id]
        ]);

        $this->actingAs($this->mainUser)
            ->get('me/associate-of')
            ->assertStatus(200)
            ->assertSeeAll([
                'href="'.url('/accounts/'.$user1->id).'"',
                'href="'.url('/accounts/'.$user2->id).'"',
            ], 'Missing account links')
            ->assertDontSeeAll($links, 'Invalid acount links');
    }
}
