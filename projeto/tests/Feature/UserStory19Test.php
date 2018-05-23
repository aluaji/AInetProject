<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user, when I change the start balance of an account, I want the application to recalculate all start and end
 * balances of all movements of the account, as well as the current balance of that account.
 */
class UserStory19Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function it_updates_current_balance_on_an_account_without_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 1.23,
            'current_balance' => 1.23,
        ]);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 1.24,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 1.24,
            'current_balance' => 1.24,
            'last_movement_date' => $account->last_movement_date
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_keeps_current_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 0.1,
            'current_balance' => -111,
        ]);
        $movements = $this->seedTransactions($account, 'expense', 1111);
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 0.1,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 0.1,
            'current_balance' => -111,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, 0);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_increases_current_balance_1()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 0.1,
            'current_balance' => -111,
        ]);
        $movements = $this->seedTransactions($account, 'expense', 1111);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 1.23,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 1.23,
            'current_balance' => -109.87,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, 113);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_increases_current_balance_2()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 0.1,
            'current_balance' => 111.20,
        ]);
        $movements = $this->seedTransactions($account, 'revenue', 1111);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 1.23,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 1.23,
            'current_balance' => 112.33,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, 113);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_increases_current_balance_3()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 0.1
        ]);
        $movements = $this->seedTransactions($account, 'mixed', 1111);
        $sum = $movements->reduce(function ($carry, $t) {
            if ($t->type == 'revenue') {
                return $carry + to_cents($t->value);
            }
            return $carry - to_cents($t->value);
        });
        $account->current_balance = (to_cents($account->start_balance) + $sum) / 100.0;
        DB::table('accounts')->update((array)$account);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 1.23,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 1.23,
            'current_balance' => (123 + $sum) / 100.0,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, 113);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_decreases_current_balance_1()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 111.10,
            'current_balance' => 0,
        ]);
        $movements = $this->seedTransactions($account, 'expense', 1111);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => -1.23,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => -1.23,
            'current_balance' => -112.33,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, -11233);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_decreases_current_balance_2()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 1.1,
            'current_balance' => 112.2,
        ]);
        $movements = $this->seedTransactions($account, 'revenue', 1111);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => -1.23,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => -1.23,
            'current_balance' => 109.87,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, -233);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_decreases_current_balance_3()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 1.23
        ]);
        $movements = $this->seedTransactions($account, 'mixed', 1111);
        $sum = $movements->reduce(function ($carry, $t) {
            if ($t->type == 'revenue') {
                return $carry + to_cents($t->value);
            }
            return $carry - to_cents($t->value);
        });
        $account->current_balance = (to_cents($account->start_balance) + $sum) / 100.0;
        DB::table('accounts')->update((array)$account);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 0.1,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 0.1,
            'current_balance' => (10 + $sum) / 100.0,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, -113);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_updates_current_balance_in_order()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'code' => $this->faker->uuid,
            'start_balance' => 0,
            'current_balance' => 111.1,
        ]);
        $movements = $this->seedTransactions($account, 'revenue', 1111, 2000, -1);

        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => 0.1,
        ];

        $this->actingAs($this->mainUser)
            ->put('/account/'.$account->id, $data)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'start_balance' => 0.1,
            'current_balance' => 111.2,
            'last_movement_date' => $account->last_movement_date
        ]);

        $this->assertsMovements($movements, 10);
    }

}
