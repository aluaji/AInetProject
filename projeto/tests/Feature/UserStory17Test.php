<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user I want to create accounts. Account data should include the account type, the date when the account was
 * created (not necessarily the current date), the account code and the start balance of the account.
 * It might also include a description;
 */
class UserStory17Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_create_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now(),
            'start_balance' => 20,
            'description' => 'Random',
        ];

        $this->post('/account', $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->actingAs($this->mainUser)
            ->post('/account')
            ->assertSessionHasErrors(['account_type_id', 'code', 'start_balance'])
            ->assertSessionHasNoErrors(['description', 'date']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_fails_with_invalid_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => 20,
            'code' => $this->faker->uuid,
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSessionHasErrors(['account_type_id'])
            ->assertSessionHasNoErrors(['description', 'code', 'date', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_use_proper_rule_to_validate_account_type_id()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        DB::table('account_types')->insert(['id' => 2000000, 'name' => 'just a new type']);

        $data = [
            'account_type_id' => 2000000,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSessionHasNoErrors(['account_type_id']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_fails_with_non_unique_code()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $accounts->first()->code,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSessionHasErrors(['code'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'date', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_fails_with_invalid_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $this->faker->uuid,
            'date' => '99999999',
            'start_balance' => 0,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSessionHasErrors(['date'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'code', 'start_balance']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_fails_with_invalid_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now(),
            'start_balance' => '8989asdasd',
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSessionHasErrors(['start_balance'])
            ->assertSessionHasNoErrors(['description', 'account_type_id', 'code', 'date']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_account_with_zero_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'start_balance' => 0,
        ];
        $minDate = Carbon::now();
        $minDate->setTime($minDate->hour, $minDate->minute, $minDate->second);

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);

        $storedAccount = DB::table('accounts')->where($data)->first();

        $this->assertNotNull($storedAccount->date);
        $this->assertTrue(Carbon::createFromFormat('Y-m-d', $storedAccount->date) >= $minDate);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_account_with_a_specific_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_account_with_negative_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => -1.23,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_account_with_positive_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 3.21,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_code_can_be_repeated_across_users()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedUserAccount($this->adminUser->id, $this->types[0]->id, ['code' => 'test-code']);
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => 'test-code',
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 3.21,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_account_with_a_description()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 3.21,
            'description' => $this->faker->realText(50)
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_create_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'start_balance' => 1000,
        ];

        $this->actingAs($this->adminUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->adminUser->id;
        $data['current_balance'] = $data['start_balance'];
        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_have_multiple_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedClosedAccountsForUser($this->adminUser->id);
        $this->seedOpenedAccountsForUser($this->mainUser->id);
        $data = [
            'account_type_id' => $this->types[3]->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'start_balance' => 1000,
        ];

        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect()
            ->assertSessionHasNoErrors('account_type_id');

        $this->assertDatabaseHas('accounts', $data);
        $this->assertEquals(6, DB::table('accounts')->count());
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_cannot_impersonate_owner()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'owner_id' => $this->adminUser->id,
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['owner_id'] = $this->mainUser->id;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_cannot_set_current_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'owner_id' => $this->mainUser->id,
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 1,
            'current_balance' => 0,
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['current_balance'] = 1;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_cannot_set_last_movement_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'owner_id' => $this->mainUser->id,
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
            'last_movement_date' => Carbon::now()->format('Y-m-d'),
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['last_movement_date'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_creation_cannot_set_deleted_at()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $data = [
            'owner_id' => $this->mainUser->id,
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_balance' => 0,
            'deleted_at' => Carbon::now()->format('Y-m-d'),
        ];
        $this->actingAs($this->mainUser)
            ->post('/account', $data)
            ->assertSuccessfulOrRedirect();

        $data['deleted_at'] = null;

        $this->assertDatabaseHas('accounts', $data);
    }
}
