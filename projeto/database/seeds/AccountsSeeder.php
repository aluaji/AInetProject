<?php

use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    private $minBalance = 10000;
    private $maxBalance = 1000000;

    private $numberOfAccountsWithZeroStartBalance = 15;
    private $numberOfAccountsWithPositiveStartBalance = 15;
    private $numberOfAccountsWithNegativeStartBalance = 10;
    private $numberOfDeletedAccounts = 5;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Accounts table seeder notice'], [
            ['Edit this file to change the number of accounts to create']
        ]);

        $faker = Faker\Factory::create('pt_PT');

        $accountTypes = DB::table('account_types')->pluck('id');
        $owners = DB::table('users')->pluck('id');

        $this->command->info('Creating '.
                             $this->numberOfAccountsWithZeroStartBalance.' accounts with zero start balance...');
        for ($i = 0; $i < $this->numberOfAccountsWithZeroStartBalance; ++$i) {
            $account = $this->fakeAccount($faker, $accountTypes->random(), $owners->random());
            DB::table('accounts')->insert($account);
        }
        $this->command->info('');

        $this->command->info('Creating '.
                             $this->numberOfAccountsWithPositiveStartBalance.' accounts with zero start balance...');
        for ($i = 0; $i < $this->numberOfAccountsWithPositiveStartBalance; ++$i) {
            $account = $this->fakeAccount($faker, $accountTypes->random(), $owners->random());
            $amount = $faker->numberBetween($this->minBalance, $this->maxBalance) / 100.0;
            $account['start_balance'] = $account['current_balance'] = $amount;
            DB::table('accounts')->insert($account);
        }
        $this->command->info('');

        $this->command->info('Creating '.
                             $this->numberOfAccountsWithNegativeStartBalance.' accounts with zero start balance...');
        for ($i = 0; $i < $this->numberOfAccountsWithNegativeStartBalance; ++$i) {
            $account = $this->fakeAccount($faker, $accountTypes->random(), $owners->random());
            $amount = $faker->numberBetween(-$this->maxBalance, -$this->minBalance) / 100.0;
            $account['start_balance'] = $account['current_balance'] = $amount;
            DB::table('accounts')->insert($account);
        }
        $this->command->info('');

        $this->command->info('Creating '.
                             $this->numberOfDeletedAccounts.' accounts with zero start balance...');
        for ($i = 0; $i < $this->numberOfDeletedAccounts; ++$i) {
            $account = $this->fakeAccount($faker, $accountTypes->random(), $owners->random());
            $amount = $faker->numberBetween(-$this->maxBalance, $this->maxBalance) / 100.0;
            $account['start_balance'] = $account['current_balance'] = $amount;
            $account['deleted_at'] = $faker->dateTimeBetween($account['date']);
            DB::table('accounts')->insert($account);
        }
        $this->command->info('');
    }

    private function fakeAccount(Faker\Generator $faker, $accountTypeId, $ownerId)
    {
        $reference = Carbon\Carbon::now()->subDays(30);
        return [
            'owner_id' => $ownerId,
            'account_type_id' => $accountTypeId,
            'date' => $faker->dateTimeBetween($reference),
            'created_at' => $faker->dateTimeBetween($reference),
            'code' => str_random(10),
            'description' => $faker->randomElement([null, $faker->realText($faker->numberBetween(10, 50))])
        ];
    }
}
