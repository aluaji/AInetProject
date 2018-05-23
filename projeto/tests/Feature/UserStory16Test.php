<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to reopen a closed account.
 */
class UserStory16Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_reopen_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);

        $this->patch('/account/'.$account->id.'/reopen')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_reopen_fails_if_the_account_is_invalid()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->mainUser)
            ->patch('/account/232/reopen')
            ->assertStatus(404); // Assumes implicit model binding
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function regular_users_can_reopen_a_closed_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);

        $this->actingAs($this->mainUser)
            ->patch('/account/'.$account->id.'/reopen')
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'deleted_at' => null
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function admins_can_reopen_a_closed_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->adminUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);

        $this->actingAs($this->adminUser)
            ->patch('/account/'.$account->id.'/reopen')
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'deleted_at' => null
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_the_owner_can_reopen_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);

        $this->actingAs($this->adminUser)
            ->patch('/account/'.$account->id.'/reopen')
            ->assertForbidden();
    }
}
