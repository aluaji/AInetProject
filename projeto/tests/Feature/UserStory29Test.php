<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to add any user to my group of associate members, which will give him permission to view all
 * my financial data.
 */
class UserStory29Test extends UserStoryTestCase
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
    public function a_guest_cannot_associate_users()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->users->first()->id];
        $this->post('/me/associates', $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function user_association_fails_for_invalid_users()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => 220];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSessionHasErrors(['associated_user']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function user_association_fails_on_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->actingAs($this->mainUser)
            ->post('/me/associates')
            ->assertSessionHasErrors(['associated_user']);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function cannot_associate_to_myself()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->mainUser->id];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSessionHasErrors(['associated_user']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function user_association_fails_if_already_associated()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users->first()->id,
        ]);
        $data = ['associated_user' => $this->users->first()->id];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSessionHasErrors(['associated_user']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_associate_another_regular_user()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->users[0]->id];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('associate_members', [
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $data['associated_user'],
        ]);

        $this->assertDatabaseMissing('associate_members', [
            'created_at' => null,
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_associate_a_blocked_user()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->users[2]->id];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('associate_members', [
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $data['associated_user'],
        ]);

        $this->assertDatabaseMissing('associate_members', [
            'created_at' => null,
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_associate_an_admin()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->users[1]->id];
        $this->actingAs($this->mainUser)
            ->post('/me/associates', $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('associate_members', [
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $data['associated_user'],
        ]);

        $this->assertDatabaseMissing('associate_members', [
            'created_at' => null,
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_associate_a_regular_user()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = ['associated_user' => $this->users[0]->id];
        $this->actingAs($this->adminUser)
            ->post('/me/associates', $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('associate_members', [
            'main_user_id' => $this->adminUser->id,
            'associated_user_id' => $data['associated_user'],
        ]);

        $this->assertDatabaseMissing('associate_members', [
            'created_at' => null,
        ]);
    }
}
