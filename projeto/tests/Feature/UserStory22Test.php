<?php

namespace Tests\Feature;

use Carbon\Carbon;
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
abstract class UserStory22Test extends BaseAccountsTest
{
    protected $account;
    protected $keep;

    protected function setUp()
    {
        parent::setUp();
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->merge($this->seedClosedAccountsForUser($this->mainUser->id))
            ->merge($this->seedOpenedAccountsForUser($this->adminUser->id))
            ->merge($this->seedClosedAccountsForUser($this->adminUser->id));

        $this->account = $accounts->shift();
        $start = 20000;
        foreach ($accounts as $account) {
            $this->seedTransactions($account, 'mixed', 50, $start, -1);
            $start -= 200;
        }
        $this->keep = DB::table('movements')
            ->select('id', 'date', 'value', 'start_balance', 'end_balance', 'created_at')
            ->get();
    }

    protected function oldest($skip = 0)
    {
        return  DB::table('movements')
            ->where('account_id', $this->account->id)
            ->select('id', 'date', 'created_at')
            ->orderBy('date')
            ->orderBy('created_at')
            ->skip($skip)
            ->take(1)
            ->get()
            ->transform(function ($row) {
                return (object)[
                    'id' => $row->id,
                    'date' => new Carbon($row->date),
                    'created_at' => new Carbon($row->created_at),
                ];
            })
            ->first();
    }

    protected function latest($skip = 0)
    {
        return  DB::table('movements')
            ->where('account_id', $this->account->id)
            ->select('id', 'date', 'created_at')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip($skip)
            ->take(1)
            ->get()
            ->transform(function ($row) {
                return (object)[
                    'id' => $row->id,
                    'date' => new Carbon($row->date),
                    'created_at' => new Carbon($row->created_at),
                ];
            })
            ->first();
    }

    protected function setAccountBalance($start)
    {
        $this->account->start_balance = $start;
        DB::table('accounts')
            ->where('id', $this->account->id)
            ->update([
                'start_balance' => $start,
                'current_balance' => $start,
            ]);
    }

    protected function assertMovementsHasAll($rows)
    {
        foreach ($rows->toArray() as $row) {
            try {
                $this->assertDatabaseHas('movements', (array)$row);
            } catch (\RuntimeException $e) {
                var_dump($row);
                $this->dump();
                throw $e;
            }
        }
    }

    protected function assertTransactions($transactions)
    {
        $balance = to_cents($this->account->start_balance);
        $transactions = $transactions->toArray();
        usort($transactions, function ($tx1, $tx2) {
            if ($tx1->date < $tx2->date) {
                return -1;
            }
            if ($tx1->date > $tx2->date) {
                return 1;
            }
            if (isset($tx1->created_at) && isset($tx2->created_at)) {
                return $tx1->created_at <=> $tx2->created_at;
            }
            return $tx1->id <=> $tx2->id;
        });

        foreach ($transactions as $t) {
            $t->start_balance = $balance / 100.0;
            if ($t->type == 'revenue') {
                $balance += to_cents($t->value);
            } else {
                $balance -= to_cents($t->value);
            }
            $t->end_balance = $balance / 100.0;
        }
        $this->assertMovementsHasAll(collect($transactions));
    }

    protected function dump()
    {
        $transactions = DB::table('movements')
            ->where('account_id', $this->account->id)
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();
        // dd($transactions);
    }

    protected function startQueryLogging()
    {
        DB::connection()->enableQueryLog();
        $this->queries = collect();
    }

    protected function stopQueryLogging()
    {
        $this->queries = collect(DB::getQueryLog());
        DB::connection()->disableQueryLog();
        DB::connection()->flushQueryLog();
    }

    protected function assertQueryDateClause(Carbon $date)
    {
        $geDate = $date->format('Y-m-d');
        $gDate = $date->subDays(1)->format('Y-m-d');
        $queries = $this->queries->filter(function ($entry) {
            $query = $entry['query'];
            return
                preg_match('/from ["`]movements["`]/', $query) &&
                preg_match('/["`]date["`]/', $query) &&
                !str_contains($query, ' limit ');
        });

        if ($queries->count() == 0) {
            $this->fail('Missing date clause on movements query1');
        }
        foreach ($queries as $query) {
            $dateClause = preg_match('/["`]date["`] [>][=]/', $query['query']) ? $geDate : $gDate;
            $hasDateClause = collect($query['bindings'])->contains(function ($value) use ($dateClause) {
                return starts_with($value, $dateClause);
            });
            if (!$hasDateClause) {
                $this->fail('Missing date clause on movements query');
            }
        }
    }
}
