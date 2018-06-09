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
class UserStory22BTest extends UserStory22Test
{
    protected function propagateEditAt($transactions, $type, $value, $split, $insertion, $lastDate = null, $skipAssert = false)
    {
        $movement = $transactions->where('id', $split->id)->first();
        $oldValue = $movement->type == 'revenue' ? $movement->value : -$movement->value;
        $newValue = $type == 'revenue' ? $value : -$value;

        $movement->movement_category_id = $this->categories[$type]->random()->id;
        $movement->type = $type;
        $movement->date = $insertion->format('Y-m-d');
        $movement->value = $value;

        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
        ];

        if ($skipAssert) {
            $this->startQueryLogging();
        }
        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSuccessfulOrRedirect();

        if ($skipAssert) {
            $this->stopQueryLogging();
            return;
        }

        $this->assertMovementsHasAll($this->keep);
        $this->assertTransactions($transactions);
        $delta = to_cents($newValue) - to_cents($oldValue);
        $this->assertEquals(
            $transactions->count(),
            DB::table('movements')->where('account_id', $this->account->id)->count()
        );
        $this->assertDatabaseHas('accounts', [
            'id' => $this->account->id,
            'start_balance' => $this->account->start_balance,
            'current_balance' => (to_cents($this->account->current_balance) + $delta) / 100.0,
            'last_movement_date' => is_null($lastDate) ? $this->account->last_movement_date : $lastDate
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_head_day_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $split->date->subDays(1));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_head_minute_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $split->date->subMinutes(1));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_head_day_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $split->date->subDays(1));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_head_minute_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest();
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $split->date->subMinutes(1));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_tail_day_after()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->latest();
        $insertion = $split->date->addDays(1);
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $insertion, $insertion->format('Y-m-d'));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_tail_minute_after()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->latest();
        $insertion = $split->date->addMinutes(1);
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $insertion, $insertion->format('Y-m-d'));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_tail_day_after()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->latest();
        $insertion = $split->date->addDays(1);
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $insertion, $insertion->format('Y-m-d'));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_tail_minute_after()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->latest();
        $insertion = $split->date->addMinutes(1);
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $insertion, $insertion->format('Y-m-d'));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_middle_day_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest(5);
        $insertion = $split->date->subDays(1);
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $insertion);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_revenue_at_middle_minute_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0, -1);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest(5);
        $insertion = $split->date->subMinutes(1);
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $insertion);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_middle_day_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(1, 2);
        $trx = $this->seedTransactions($this->account, 'revenue', 10, 50, -1);
        $split = $this->oldest(5);
        $insertion = $split->date->subDays(1);
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $insertion);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function propagate_edit_expense_at_middle_minute_before()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->setAccountBalance(0, -1);
        $trx = $this->seedTransactions($this->account, 'expense', 10, 50, -1);
        $split = $this->oldest(5);
        $insertion = $split->date->subMinutes(1);
        $this->propagateEditAt($trx, 'expense', 0.25, $split, $insertion);
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
        $insertion = $split->date->addDays(1);
        $this->propagateEditAt($trx, 'revenue', 0.25, $split, $insertion, $insertion->format('Y-m-d'), true);
        $this->assertQueryDateClause($insertion);
    }
}
