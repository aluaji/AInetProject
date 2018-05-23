<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

if (!function_exists("to_cents")) {
    function to_cents($value)
    {
        return bcmul($value, 100, 0);
    }
}


class UserStoryTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $mainUser;
    protected $adminUser;
    protected $response;

    protected function setUp()
    {
        parent::setUp();

        $this->seed(\TypesSeeder::class);
    }

    protected function seedMainUser()
    {
        $this->mainUser = factory(User::class)->create([
            'name' => 'regular user',
            'email' => 'user@mail.pt',
            'password' => bcrypt('abc')
        ]);
    }

    protected function seedAdminUser()
    {
        $this->adminUser = factory(User::class)->create([
            'email' => 'iamroot@mail.pt',
            'name' => 'rootiam',
            'password' => bcrypt('fff'),
            'admin' => true
        ]);
    }

    protected function seedUser($name, $email, $admin = false, $blocked = false)
    {
        return factory(User::class)->create([
            'name' => $name,
            'email' => $email,
            'admin' => $admin,
            'blocked' => $blocked,
            'password' => bcrypt('123')
        ]);
    }

    protected function seedExtraUsers($count = 1)
    {
        $extraUsers = collect();
        for ($i = 1; $i <= $count; ++$i) {
            $extraUsers->push($this->seedUser("user{$i}", "user{$i}@mail.pt"));
        }
        return $extraUsers;
    }

    protected function seedAccounts($users, $count = 1)
    {
        foreach ($users as $user) {
            $this->seedUserAccounts($user, $count);
        }
    }

    protected function seedUserAccounts($user, $count = 1)
    {
        $accountTypes = DB::table('account_types')->pluck('id')->shuffle();
        $limit = min($count, $accountTypes->count());

        for ($i = 1; $i <= $limit; ++$i) {
            $this->seedUserAccount($user->id, $accountTypes->pop());
        }
    }

    protected function seedUserAccount($userId, $typeId, $customData = [])
    {
        $reference = Carbon::now()->subDays(60);
        $data = [
            'owner_id' => $userId,
            'account_type_id' => $typeId,
            'date' => $this->faker->dateTimeInInterval($reference, '+15 days')->format('Y-m-d'),
            'created_at' => $this->faker->dateTimeBetween($reference),
            'code' => $this->faker->uuid,
        ];
        $data = array_merge($data, $customData);
        $id = DB::table('accounts')->insertGetId($data);
        return DB::table('accounts')
            ->where('id', $id)
            ->first();
    }

    protected function seedMovements($accounts, $count = 1)
    {
        foreach ($accounts as $account) {
            $this->seedAccountMovements($account, $count);
        }
    }

    protected function seedAccountMovements($account, $count = 1)
    {
        $expenses = DB::table('movement_categories')->where('type', 'expense')->pluck('id');
        $revenues = DB::table('movement_categories')->where('type', 'revenue')->pluck('id');
        $balance = to_cents($account->start_balance);
        $date = Carbon::createFromFormat('Y-m-d', $account->date)->startOfDay();

        for ($i = 1; $i <= $count; ++$i) {
            $isExpense = $this->faker->boolean;
            $amount = $this->faker->numberBetween(5, 50000);

            $account_id = $account->id;
            $movement_category_id = $isExpense ? $expenses->random() : $revenues->random();
            $date = $date->addMinutes($this->faker->numberBetween(0, 48 * 60));
            $value = $isExpense ? -$amount : $amount;
            $start_balance = $balance / 100.0;
            $balance += $value;
            $end_balance = $balance / 100.0;
            $value = abs($value / 100.0);
            $description = null;
            $type = $isExpense ? 'expense' : 'revenue';
            $created_at = $date;

            DB::table('movements')->insert(
                compact(
                    'account_id',
                    'movement_category_id',
                    'date',
                    'value',
                    'start_balance',
                    'end_balance',
                    'description',
                    'type',
                    'created_at'
                )
            );
        }
    }
}
