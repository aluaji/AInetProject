<?php


use Illuminate\Database\Seeder;

class MembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('associate_members')->truncate();
        $users = DB::table('users')
            ->pluck('id')
            ->shuffle();

        $faker = Faker\Factory::create('pt_PT');

        $numberOfCandidates = (int)($users->count() / 3);
        $candidates = collect();
        // For loop to enable repetitions
        for ($i=0; $i < $numberOfCandidates; $i++) {
            $candidates->push($users->random());
        }
        $associates = $users->diff($candidates)->random($candidates->count());

        $candidates
            ->zip($associates)
            ->each(function ($pair) use ($faker) {
                DB::table('associate_members')->insert([
                    'main_user_id' => $pair[0],
                    'associated_user_id' => $pair[1],
                    'created_at' => $faker->dateTime()
                ]);
            });

        $candidates
            ->shuffle()
            ->zip($associates)
            ->each(function ($pair) use ($faker) {
                DB::table('associate_members')->insert([
                    'main_user_id' => $pair[1],
                    'associated_user_id' => $pair[0],
                    'created_at' => $faker->dateTime()
                ]);
            });
    }
}
