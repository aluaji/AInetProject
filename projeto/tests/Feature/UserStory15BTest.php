<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to delete (if it has no movements) or close an opened account.
 */
class UserStory15BTest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_close_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->patch('/account/'.$account->id.'/close')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_close_fails_if_the_account_is_invalid()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->mainUser)
            ->patch('/account/232/close')
            ->assertStatus(404); // Assumes implicit model binding
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function regular_users_can_close_an_opened_account_with_no_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->mainUser)
            ->patch('/account/'.$account->id.'/close')
            ->assertSuccessfulOrRedirect();

        $this->assertSoftDeleted('accounts', ['id' => $account->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function admins_can_close_an_account_with_no_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->adminUser->id, $this->types[0]->id);

        $this->actingAs($this->adminUser)
            ->patch('/account/'.$account->id.'/close')
            ->assertSuccessfulOrRedirect();

        $this->assertSoftDeleted('accounts', ['id' => $account->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_the_owner_can_close_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->adminUser)
            ->patch('/account/'.$account->id.'/close')
            ->assertForbidden();
    }
}
