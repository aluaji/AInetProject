<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    private $photoPath = 'public/profiles';
    private $numberOfNonAdminUsers = 20;
    private $numberOfAdminUsers = 5;
    private $numberOfBlockedUsers = 5;

    private $photos_index = -1;
    private $photos = array(
        "02932e1bb110688d8c568752381841c4.jpg",
        "03f4f1ce33f1fad787b7fe84fc76685f.jpg",
        "0af446a72954880e47f12ad9bd719d8b.jpg",
        "13f956307149ccfa40e6d3e4f426bd1d.jpg",
        "19493ec576659f119ccf19b8a4e4f1a7.jpg",
        "22ad89b4a69377e6475bdd92254cc9a4.jpg",
        "2693a9409293bdf53b1ab37b3fb61a3e.jpg",
        "2f2b30fb4909c1cf2412f385dc671446.jpg",
        "3c8a39741f5870191dc93696ef1ab74d.jpg",
        "4177855435679e06fe3b799aa1a900de.jpg",
        "443ca91d396f68ae93014940e2c2c07b.jpg",
        "469a3543a793c47ba1f39694f5cc9ae8.jpg",
        "47785612333aa49a86749a2f71fa4c8d.jpg",
        "47d7a12e9cd781b27f25ddb7b783542b.jpg",
        "754c9b3d7650a5ef20873903442a082e.jpg",
        "77a8f3abe9679c37309f8874b7513b42.jpg",
        "8449be690352a45db88e83107406fc83.jpg",
        "9350082cd28c6dbe09b7e8d077f884b6.jpg",
        "964ded74dad4abddea86d5375c45cd0a.jpg",
        "9f512ba15ddaeedf91e193409633ce2f.jpg",
        "a39b4cf698652d5c3f4ef3853e0d5cb3.jpg",
        "add827838ae2369980135f58aae569a8.jpg",
        "b02ca14e82bc8efb14ba7918e96cab01.jpg",
        "bd89daca446719258ff0d6a65b29b57b.jpg",
        "deb886e1f3c12f285e5eeb4395a2cbb0.jpg",
        "e620f1001abbf735e647911bdc9ce2b3.jpg",
        "e67453d7d0102e617afb34134eeceb8e.jpg",
        "e7bd338d86167f74e024b2c50c5b3521.jpg",
        "e9c7ff9251ff809cc91ed4e7d683786e.jpg",
        "fac04f666a3ef3fd5966cf938813ef6b.jpg",
    );

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

        $this->photos_index++;

        return [
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => $password ?: $password = bcrypt('secret'),
            'remember_token' => str_random(10),
            'phone' => $faker->randomElement([null, $faker->phoneNumber]),
            'profile_photo' => $this->photos[ $this->photos_index ],
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
