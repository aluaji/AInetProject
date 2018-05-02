<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(MembersSeeder::class);
        $this->call(TypesSeeder::class);
        $this->call(AccountsSeeder::class);
        $this->call(TransactionsSeeder::class);
        $this->call(DocumentsSeeder::class);
    }
}
