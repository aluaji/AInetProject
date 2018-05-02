<?php

use Illuminate\Database\Seeder;

class TypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('account_types')->insert([
            ['name' => 'Bank account'],
            ['name' => 'Pocket money'],
            ['name' => 'PayPal account'],
            ['name' => 'Credit card'],
            ['name' => 'Meal card'],
        ]);

        DB::table('movement_categories')->insert([
            ['type' => 'expense', 'name' => 'food'],
            ['type' => 'expense', 'name' => 'clothes'],
            ['type' => 'expense', 'name' => 'services'],
            ['type' => 'expense', 'name' => 'electricity'],
            ['type' => 'expense', 'name' => 'phone'],
            ['type' => 'expense', 'name' => 'fuel'],
            ['type' => 'expense', 'name' => 'insurance'],
            ['type' => 'expense', 'name' => 'entertainment'],
            ['type' => 'expense', 'name' => 'culture'],
            ['type' => 'expense', 'name' => 'trips'],
            ['type' => 'expense', 'name' => 'mortgage payment'],
        ]);

        DB::table('movement_categories')->insert([
            ['type' => 'revenue', 'name' => 'salary'],
            ['type' => 'revenue', 'name' => 'bonus'],
            ['type' => 'revenue', 'name' => 'royalties'],
            ['type' => 'revenue', 'name' => 'interests'],
            ['type' => 'revenue', 'name' => 'gifts'],
            ['type' => 'revenue', 'name' => 'dividends'],
            ['type' => 'revenue', 'name' => 'product sales'],
        ]);
    }
}
