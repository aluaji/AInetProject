<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to edit the data of accounts, namely its type, code (always unique for each user),
 * description and start balance.
 */
class UserStory18Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_update_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => 'new code',
            'date' => Carbon::now()->subDays(2),
            'start_balance' => $account->start_balance + 0.1,
            'description' => 'new description',
        ];

        $this->put('/account/'.$account->id, $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id)
            ->assertSessionHasErrors(['account_type_id', 'code', 'date', 'start_balance'])
            ->assertSessionHasNoErrors(['description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_invalid_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->mainUser)
            ->put('/account/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_invalid_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => 200000,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSessionHasErrors(['account_type_id'])
            ->assertSessionHasNoErrors(['description', 'code', 'date', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_non_unique_code()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $accounts->last()->code,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$accounts->first()->id, $data)
            ->assertSessionHasErrors(['code'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'date', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_invalid_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $this->faker->uuid,
            'date' => '99999999',
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSessionHasErrors(['date'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'code', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_with_invalid_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now(),
            'start_balance' => '8989asdasd',
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSessionHasErrors(['start_balance'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'code', 'date']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_account_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'account_type_id' => $data['account_type_id']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_keep_account_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $account->account_type_id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'account_type_id' => $data['account_type_id']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_account_code()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedUserAccount($this->adminUser->id, $this->types[0]->id, ['code' => 'test-code']);
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => 'test-code',
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect()
            ->assertSessionHasNoErrors(['code']);

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'code' => $data['code']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_keep_account_code()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $account->code,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'code' => $data['code']]);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_account_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => $account->start_balance,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'date' => $data['date']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_account_start_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 98765.43,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'start_balance' => $data['start_balance']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_description()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
            'description' => 'new description'
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'description' => $data['description']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_update_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
            'description' => 'new description'
        ];

        $this->actingAs($this->adminUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'description' => $data['description']]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_cannot_impersonate_owner()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'owner_id' => $this->adminUser->id,
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data);

        $this->assertDatabaseMissing('accounts', ['id' => $account->id, 'owner_id' => $this->adminUser->id]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_if_account_is_not_owned()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];
        $this->actingAs($this->adminUser)
            ->put('/account/'.$account->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_cannot_set_current_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
            'current_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data);

        $this->assertDatabaseMissing('accounts', ['id' => $account->id, 'current_balance' => 0]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_cannot_set_last_movement_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
            'last_movement_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data);

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
            'last_movement_date' => $data['last_movement_date']
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_cannot_set_deleted_at()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
            'deleted_at' => Carbon::now()->subDays(10),
        ];
        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data);

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
            'deleted_at' => $data['deleted_at']
        ]);
    }
}
