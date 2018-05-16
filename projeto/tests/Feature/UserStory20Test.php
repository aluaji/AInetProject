<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to view all movements of an account. The listing must show at least the following fields: category,
 * date, value, type and end_balance.
 */
class UserStory20Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_get_movements_index()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->get('/movements/'.$account->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movements_index_fails_with_invalid_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->mainUser)
            ->get('/movements/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_get_movements_index()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'mixed', 8);

        $this->response = $this->actingAs($this->mainUser)
            ->get('/movements/'.$account->id)
            ->assertSuccessful();

        $this->assertOrderedMovements();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_user_can_get_movements_index()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $this->seedTransactions($account, 'mixed', 8);

        $this->response = $this->actingAs($this->adminUser)
            ->get('/movements/'.$account->id)
            ->assertSuccessful();

        $this->assertOrderedMovements();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_user_cannot_get_movements_of_unowned_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'mixed', 8);

        $this->actingAs($this->adminUser)
            ->get('/movements/'.$account->id)
            ->assertForbidden();
    }
}
