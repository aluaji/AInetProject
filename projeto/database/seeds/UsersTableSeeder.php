<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    private $photoPath = 'public/profiles';
    private $numberOfNonAdminUsers = 20;
    private $numberOfAdminUsers = 5;
    private $numberOfBlockedUsers = 5;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Users table seeder notice'], [
            ['Profile photos will be stored on path '.storage_path('app/'.$this->photoPath)],
            ['A progress bar is displayed because photos will be downloaded from lorempixel'],
            ['Edit this file to change the storage path or the number of users']
        ]);


        if ($this->command->confirm('Do you wish to delete photos from '
                                    .storage_path('app/'.$this->photoPath).'?', true)) {
            Storage::deleteDirectory($this->photoPath);
        }
        Storage::makeDirectory($this->photoPath);

        // Disclaimer: I'm using faker here because Model classes are developed by students
        $faker = Faker\Factory::create('pt_PT');


        $this->command->info('Creating '.$this->numberOfNonAdminUsers.' active users...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfNonAdminUsers);
        for ($i = 0; $i < $this->numberOfNonAdminUsers; ++$i) {
            DB::table('users')->insert($this->fakeUser($faker));
            $bar->advance();
        }
        $bar->finish();
        $this->command->info('');

        $this->command->info('Creating '.$this->numberOfAdminUsers.' active admins...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfAdminUsers);
        for ($i = 0; $i < $this->numberOfAdminUsers; ++$i) {
            $user = $this->fakeUser($faker);
            $user['admin'] = true;
            DB::table('users')->insert($user);
            $bar->advance();
        }
        $bar->finish();
        $this->command->info('');

        $this->command->info('Creating '.$this->numberOfBlockedUsers.' blocked users...');
        $bar = $this->command->getOutput()->createProgressBar($this->numberOfBlockedUsers);
        for ($i = 0; $i < $this->numberOfBlockedUsers; ++$i) {
            $user = $this->fakeUser($faker);
            $user['blocked'] = true;
            DB::table('users')->insert($user);
            $bar->advance();
        }
        $bar->finish();
        $this->command->info('');
    }

    private function fakeUser(Faker\Generator $faker)
    {
        static $password;
        $createdAt = Carbon\Carbon::now()->subDays(30);
        $updatedAt = $faker->dateTimeBetween($createdAt);
        return [
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => $password ?: $password = bcrypt('secret'),
            'remember_token' => str_random(10),
            'phone' => $faker->randomElement([null, $faker->phoneNumber]),
            'profile_photo' => $faker->randomElement([
                null,
                $faker->image(storage_path('app/'.$this->photoPath), 180, 180, 'people', false)
            ]),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
