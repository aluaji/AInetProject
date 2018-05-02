<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionsSeeder extends Seeder
{
    private $minMovements = 0;
    private $maxMovements = 30;
    private $minAmountInCents = 5;
    private $maxAmountInCents = 50000;
    private $accountsProportion = 0.5;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Movements table seeder notice'], [
            ['Edit this file to change the number of financial movements to create']
        ]);

        $faker = Faker\Factory::create('pt_PT');

        $accounts = DB::table('accounts')->get();

        $selected = $accounts->random((int)($accounts->count() * $this->accountsProportion));

        $expenses = DB::table('movement_categories')->where('type', 'expense')->pluck('id');
        $revenues = DB::table('movement_categories')->where('type', 'revenue')->pluck('id');

        foreach ($selected as $account) {
            $this->fakeMovements($faker, $account, $expenses, $revenues);
        }
    }

    private function fakeMovements(Faker\Generator $faker, $account, $expenses, $revenues)
    {
        $balance = (int)($account->start_balance * 100);
        $date = Carbon::createFromFormat('Y-m-d', $account->date)->startOfDay();
        $count = $faker->numberBetween($this->minMovements, $this->maxMovements);
        if (!$count) {
            return;
        }
        $this->command->info("Creating $count financial movements for account {$account->code}...");
        while ($count > 0) {
            $count--;
            $isExpense = $faker->boolean;
            $amount = $faker->numberBetween($this->minAmountInCents, $this->maxAmountInCents);

            $account_id = $account->id;
            $movement_category_id = $isExpense ? $expenses->random() : $revenues->random();
            $date = $date->addMinutes($faker->numberBetween(0, 48 * 60));
            $value = $isExpense ? -$amount : $amount;
            $start_balance = $balance / 100.0;
            $balance += $value;
            $end_balance = $balance / 100.0;
            $value /= 100.0;
            $description = $faker->randomElement([null, $faker->realText($faker->numberBetween(10, 25))]);
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

        DB::table('accounts')
            ->where('id', $account->id)
            ->update([
                'current_balance' => $end_balance,
                'last_movement_date' => $date
            ]);
    }
}
