<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * US 30.   As a user I want to remove any user from my group of associate members, which will inhibit him from
 * accessing my financial data.
 */
class UserStory30Test extends UserStoryTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->seedMainUser();
        $this->seedAdminUser();
        $this->users = collect([
            $this->seedUser('user1', 'user1@mail.pt'),
            $this->seedUser('user2', 'user2@mail.pt', true),
            $this->seedUser('user3', 'user3@mail.pt', false, true),
            $this->seedUser('user4', 'user4@mail.pt', true, true),
        ]);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_delete_an_association()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->delete('/me/associates/'.$this->users[0]->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function association_removal_fails_for_invalid_users()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->actingAs($this->mainUser)
            ->delete('/me/associates/200')
            ->assertNotFound();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function user_association_fails_if_not_present()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ];
        DB::table('associate_members')->insert($data);
        $this->actingAs($this->mainUser)
            ->delete('/me/associates/'.$this->users[1]->id)
            ->assertNotFound();

        $this->assertDatabaseHas('associate_members', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_remove_an_association()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $keep = [
            [
                'main_user_id' => $this->mainUser->id,
                'associated_user_id' => $this->users[1]->id,
            ],
            [
                'main_user_id' => $this->mainUser->id,
                'associated_user_id' => $this->users[2]->id,
            ],
            [
                'main_user_id' => $this->users[0]->id,
                'associated_user_id' => $this->mainUser->id,
            ],
        ];

        DB::table('associate_members')->insert($keep);
        $data = [
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ];
        DB::table('associate_members')->insert($data);
        $this->actingAs($this->mainUser)
            ->delete('/me/associates/'.$this->users[0]->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('associate_members', $data);
        foreach ($keep as $row) {
            $this->assertDatabaseHas('associate_members', $row);
        }
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_remove_an_association()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $keep = [
            [
                'main_user_id' => $this->adminUser->id,
                'associated_user_id' => $this->users[1]->id,
            ],
            [
                'main_user_id' => $this->adminUser->id,
                'associated_user_id' => $this->users[2]->id,
            ],
            [
                'main_user_id' => $this->users[0]->id,
                'associated_user_id' => $this->adminUser->id,
            ],
        ];

        DB::table('associate_members')->insert($keep);
        $data = [
            'main_user_id' => $this->adminUser->id,
            'associated_user_id' => $this->users[0]->id,
        ];
        DB::table('associate_members')->insert($data);
        $this->actingAs($this->adminUser)
            ->delete('/me/associates/'.$this->users[0]->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('associate_members', $data);
        foreach ($keep as $row) {
            $this->assertDatabaseHas('associate_members', $row);
        }
    }
}
