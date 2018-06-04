<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    private $photoPath = 'public/profiles';
    private $numberOfNonAdminUsers = 20;
    private $numberOfAdminUsers = 5;
    private $numberOfBlockedUsers = 5;
    private $files = [];

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

        $this->files = collect(File::files(database_path('seeds/profiles')));

        // Disclaimer: I'm using faker here because Model classes are developed by students
        $faker = Faker\Factory::create('pt_PT');
        factory(User::class, 1)->create([
            'email' => 'admin@ainet.pt',
            'password' => bcrypt('123'),
            'admin' => true
        ]);
        factory(User::class, 1)->create([
            'email' => 'user@ainet.pt',
            'password' => bcrypt('123'),
            'admin' => false
        ]);

        $this->command->info('Creating '.$this->numberOfNonAdminUsers.' active users...');
        for ($i = 0; $i < $this->numberOfNonAdminUsers; ++$i) {
            DB::table('users')->insert($this->fakeUser($faker));
        }
        $this->command->info('');

        $this->command->info('Creating '.$this->numberOfAdminUsers.' active admins...');
        for ($i = 0; $i < $this->numberOfAdminUsers; ++$i) {
            $user = $this->fakeUser($faker);
            $user['admin'] = true;
            DB::table('users')->insert($user);
        }
        $this->command->info('');

        $this->command->info('Creating '.$this->numberOfBlockedUsers.' blocked users...');
        for ($i = 0; $i < $this->numberOfBlockedUsers; ++$i) {
            $user = $this->fakeUser($faker);
            $user['blocked'] = true;
            DB::table('users')->insert($user);
        }
        $this->command->info('');
    }

    private function fakeUser(Faker\Generator $faker)
    {
        static $password;
        $createdAt = Carbon\Carbon::now()->subDays(30);
        $updatedAt = $faker->dateTimeBetween($createdAt);
        $profile_photo = null;
        if ($faker->boolean) {
            $file = $this->files->random();
            Storage::makeDirectory('app/'.$this->photoPath);
            $targetDir = storage_path('app/'.$this->photoPath);
            File::copy($file->getPathname(), $targetDir.'/'.$file->getFilename());
            $profile_photo = $file->getFilename();
        }

        return [
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => $password ?: $password = bcrypt('secret'),
            'remember_token' => str_random(10),
            'phone' => $faker->randomElement([null, $faker->phoneNumber]),
            'profile_photo' => $profile_photo,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
