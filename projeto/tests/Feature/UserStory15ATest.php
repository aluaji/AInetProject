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
class UserStory15ATest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_delete_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->delete('/account/'.$account->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_delete_fails_if_the_account_is_invalid()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->mainUser)
            ->delete('/account/232')
            ->assertStatus(404); // Assumes implicit model binding
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function regular_users_can_delete_an_opened_account_with_no_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->mainUser)
            ->delete('/account/'.$account->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function admins_can_delete_an_account_with_no_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->adminUser->id, $this->types[0]->id);

        $this->actingAs($this->adminUser)
            ->delete('/account/'.$account->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function only_the_owner_can_delete_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);

        $this->actingAs($this->adminUser)
            ->delete('/account/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_account_with_movements_cannot_be_deleted_1()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'last_movement_date' => Carbon::now()
        ]);

        $this->actingAs($this->mainUser)
            ->delete('/account/'.$account->id);

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_account_with_movements_cannot_be_deleted_2()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);
        DB::table('movements')->insert([
            'account_id' => $account->id,
            'movement_category_id' => $this->categories['revenue']->first()->id,
            'date' => Carbon::now(),
            'value' => 1,
            'start_balance' => 1,
            'end_balance' => 2,
        ]);

        $this->actingAs($this->mainUser)
            ->delete('/account/'.$account->id);

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }
}
