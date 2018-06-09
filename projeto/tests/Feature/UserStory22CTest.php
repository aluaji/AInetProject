<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Carbon\now;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user, when I add, edit (the type, value or date) or delete movements, I want the application to recalculate
 * the account current balance, as well as the start and end balance of all affected movements (according to the date
 * of the added, edited or deleted movement). For performance optimization purposes, students should guarantee that
 * recalculation is only applied to affected movements.
 */
class UserStory22CTest extends UserStory22Test
{
    protected function propagateDeleteAt($transactions, $split, $skipAssert = false)
    {
        $movement = $transactions->where('id', $split->id)->first();

        $transactions = $transactions->reject(function ($trx) use ($split) {
            return $trx->id == $split->id;
        });
        $delta = $movement->type == 'revenue' ? -$movement->value : $movement->value;

        if ($skipAssert) {
            $this->startQueryLogging();
        }
        $this->actingAs($this->mainUser)
            ->delete('/movement/'.$movement->id)
            ->assertSuccessfulOrRedirect();

        if ($skipAssert) {
            $this->stopQueryLogging();
            return;
        }

        $this->assertMovementsHasAll($this->keep);
        $this->assertTransactions($transactions);
        $this->assertEquals(
            $transactions->count(),
            DB::table('movements')->where('account_id', $this->account->id)->count()
        );
        $this->assertDatabaseHas('accounts', [
            'id' => $this->account->id,
            'start_balance' => $this->account->start_balance,
            'current_balance' => (to_cents($this->account->current_balance) + to_cents($delta)) / 100.0,
            'last_movement_date' => $transactions->last()->date
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_revenue_at_head()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_expense_at_head()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_revenue_at_tail()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->latest();
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_expense_at_tail()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->latest();
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_expense_at_middle()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest(5);
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_delete_revenue_at_middle()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1, 2);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->oldest(5);
        $this->propagateDeleteAt($trx, $split);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function it_should_not_process_all_movements()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 50, 3000, -1);
        $split = $this->latest();
        $this->propagateDeleteAt($trx, $split, true);
        $this->assertQueryDateClause($split->date);
    }
}
