<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Storage;
use Tests\TestCase;

abstract class BaseAccountsTest extends UserStoryTestCase
{
    protected $filesPath = 'documents';
    protected $types;
    protected $users;

    protected function setUp()
    {
        parent::setUp();

        $this->types = DB::table('account_types')->get();
        $this->flatCategories = DB::table('movement_categories')->get();
        $this->categories = $this->flatCategories->groupBy('type');
        $this->seedAdminUser();
        $this->seedMainUser();
        $this->users = collect([
            $this->seedUser('2d9e08d0', 'user1@mail.pt'),
            $this->seedUser('836d2620', 'user2@mail.pt', true),
            $this->seedUser('a90eb144', 'user3@mail.pt', false, true),
            $this->seedUser('b46c85c9', 'user4@mail.pt', true, true),
        ]);
    }

    protected function seedOpenedAccountsForUser($userId)
    {
        return collect([
            $this->seedUserAccount($userId, $this->types[0]->id, [
                'code' => $this->faker->uuid,
                'start_balance' => 1.23,
                'current_balance' => 1.23,
            ]),
            $this->seedUserAccount($userId, $this->types[1]->id, [
                'code' => $this->faker->uuid,
                'start_balance' => 1234.56,
                'current_balance' => 1234.56,
            ]),
            $this->seedUserAccount($userId, $this->types[2]->id, [
                'code' => $this->faker->uuid,
                'start_balance' => -67.89,
                'current_balance' => -67.89,
            ])
        ]);
    }

    protected function seedClosedAccountsForUser($userId)
    {
        return collect([
            $this->seedUserAccount($userId, $this->types[3]->id, [
                'code' => $this->faker->uuid,
                'start_balance' => 3.12,
                'current_balance' => 3.12,
                'deleted_at' => Carbon::now(),
            ]),
            $this->seedUserAccount($userId, $this->types[4]->id, [
                'code' => $this->faker->uuid,
                'start_balance' => 6543.21,
                'current_balance' => 6543.21,
                'deleted_at' => Carbon::now(),
            ]),
        ]);
    }

    protected function seedTransactions($account, $type, $count, $startId = 0, $inc = 1)
    {
        $expenses = $this->categories['expense']->pluck('id');
        $revenues = $this->categories['revenue']->pluck('id');
        $referenceDate = Carbon::createFromFormat('Y-m-d', $account->date)->startOfDay();
        $acc = to_cents($account->start_balance);
        $movements = collect();
        $id = $startId;
        for ($i = 1; $i <= $count; ++$i) {
            $isExpense = $this->faker->boolean;
            $amount = 10;
            switch ($type) {
                case 'expense':
                    $isExpense = true;
                    $amount = 10;
                    break;
                case 'revenue':
                    $amount = 10;
                    $isExpense = false;
                    break;
                case 'mixed':
                    $amount = $this->faker->numberBetween(10, 1000);
            }
            $account_id = $account->id;
            $movement_category_id = $isExpense ? $expenses->random() : $revenues->random();
            $referenceDate = $referenceDate->addDays(1);
            $date = $referenceDate->format('Y-m-d');
            $value = $isExpense ? -$amount : $amount;
            $start_balance = $acc / 100.0;
            $acc += $value;
            $end_balance = $acc / 100.0;
            $description = null;
            $type = $isExpense ? 'expense' : 'revenue';
            $created_at = $referenceDate;
            $value = abs($value / 100.0);
            $id += $inc;
            $data = compact(
                'id',
                'account_id',
                'movement_category_id',
                'date',
                'value',
                'start_balance',
                'end_balance',
                'description',
                'type',
                'created_at'
            );

            $id = DB::table('movements')->insertGetId($data);
            $movements[] = DB::table('movements')
                ->where('id', $id)
                ->first();
        }
        DB::table('accounts')
            ->where('id', $account->id)
            ->update([
                'current_balance' => $end_balance,
                'last_movement_date' => $date
            ]);
        $account->current_balance = $end_balance;
        $account->last_movement_date = $date;
        return $movements->sortBy('date');
    }

    protected function createPDFDocument($movement, $name, $size, $description, $tuple = false)
    {
        $file = UploadedFile::fake()->create($name, $size);
        $targetDir = $this->filesPath.'/'.$movement->account_id;
        $uniqueId = $movement->id.'.pdf';
        $file->storeAs($targetDir, $uniqueId);
        $data = [
            'original_name' => $name,
            'description' => $description,
            'created_at' => Carbon::now(),
            'type' => 'pdf'
        ];

        $id = DB::table('documents')->insertGetId($data);
        $document = DB::table('documents')
            ->where('id', $id)
            ->first();
        DB::table('movements')
            ->where('id', $movement->id)
            ->update(['document_id' => $document->id]);
        return $tuple ? [$document, $file] : $document;
    }

    protected function createPNGDocument($movement, $name, $description, $tuple = false)
    {
        $file = UploadedFile::fake()->image($name);
        $targetDir = $this->filesPath.'/'.$movement->account_id;
        $uniqueId = $movement->id.'.png';
        $file->storeAs($targetDir, $uniqueId);
        $data = [
            'original_name' => $name,
            'description' => $description,
            'created_at' => Carbon::now(),
            'type' => 'png'
        ];

        $id = DB::table('documents')->insertGetId($data);
        $document = DB::table('documents')
            ->where('id', $id)
            ->first();
        DB::table('movements')
            ->where('id', $movement->id)
            ->update(['document_id' => $document->id]);
        return $tuple ? [$document, $file] : $document;
    }

    protected function assertOrderedMovements()
    {
        $transactions = DB::table('movements')->orderBy('date', 'desc')->get();
        $orderedDates = $transactions->pluck('date')->toArray();

        $categories = $transactions->pluck('movement_category_id')->transform(function ($item) {
            return $this->flatCategories->firstWhere('id', $item)->name;
        })->toArray();


        $this->response->assertSeeInOrder($categories);
        $this->response->assertSeeInOrder($orderedDates);
        $this->response->assertSeeInOrder($transactions->pluck('value')->toArray());
        $this->response->assertSeeInOrder($transactions->pluck('type')->toArray());
        $this->response->assertSeeInOrder($transactions->pluck('end_balance')->toArray());
    }

    protected function assertsMovements($movements, $delta)
    {
        foreach ($movements as $movement) {
            $movement->start_balance =  (to_cents($movement->start_balance) + $delta) / 100.0;
            $movement->end_balance =  (to_cents($movement->end_balance) + $delta) / 100.0;
            $this->assertDatabaseHas('movements', (array)$movement);
        }
    }
}
