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
 * As a user I want to view and filter (by name) the public profile of all registered users, including administrators
 * and blocked users. Each public profile includes only the name and photo (if any) of the user. Users that belong to
 * my group of associate members should be identifiable as such, as well as users whose groups of associate members
 * I belong to.
 */
class UserStory11Test extends UserStoryTestCase
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
    public function profiles_index_is_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->get('/profiles')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_shows_names_for_all_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->mainUser)
            ->get('/profiles')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_works_with_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->adminUser)
            ->get('/profiles')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_shows_images()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.png');
        $file->store('profiles', 'public');
        $name = $file->hashName();
        $this->mainUser->profile_photo = $name;
        $this->mainUser->save();

        $this->response = $this->actingAs($this->mainUser)
            ->get('/profiles')
            ->assertSee(asset('storage/profiles/'.$name));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_supports_empty_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->response = $this->actingAs($this->mainUser)
            ->get('/profiles?name=')
            ->assertStatus(200);

        User::all()->each(function ($user) {
            $this->response->assertSee($user->name);
        });
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_supports_full_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->actingAs($this->mainUser)
            ->get('/profiles?name=rootiam')
            ->assertStatus(200)
            ->assertSee('rootiam')
            ->assertDontSeeAll(['2d9e08d0', '836d2620', 'a90eb144', 'b46c85c9']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_supports_partial_name_filter()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();

        $this->actingAs($this->mainUser)
            ->get('/profiles?name=d')
            ->assertStatus(200)
            ->assertSee('2d9e08d0')
            ->assertSee('836d2620')
            ->assertDontSeeAll(['rootiam', 'a90eb144', 'b46c85c9']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_tags_associates()
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
            ->get('/profiles')
            ->assertStatus(200)
            ->assertSeeCount('<span>associate</span>', 2, 'Associate tags not found');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_tags_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        $user2 = User::where('email', 'user2@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $user1->id, 'associated_user_id' => $this->mainUser->id],
            ['main_user_id' => $user2->id, 'associated_user_id' => $this->mainUser->id]
        ]);
        $this->response = $this->actingAs($this->mainUser)
            ->get('/profiles')
            ->assertStatus(200)
            ->assertSeeCount('<span>associate-of</span>', 2, 'Associate-of tags not found');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function profiles_index_tags_same_user_as_associate_and_associate_of()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedTestUsers();
        $user1 = User::where('email', 'user1@mail.pt')->first();
        DB::table('associate_members')->insert([
            ['main_user_id' => $user1->id, 'associated_user_id' => $this->mainUser->id],
            ['main_user_id' => $this->mainUser->id, 'associated_user_id' => $user1->id]
        ]);
        $this->response = $this->actingAs($this->mainUser)
            ->get('/profiles')
            ->assertStatus(200)
            ->assertSeeCount('<span>associate</span>', 1, 'Associate tag not found')
            ->assertSeeCount('<span>associate-of</span>', 1, 'Associate-of tag not found');
    }
}
