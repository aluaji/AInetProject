<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DocumentsSeeder extends Seeder
{
    private $filesPath = 'documents';
    private $movementsProportion = 0.1;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->table(['Documents table seeder notice'], [
            ['Document files will be stored on path '.storage_path('app/'.$this->filesPath)],
            ['Edit this file to change the number of documents to create']
        ]);

        if ($this->command->confirm('Do you wish to delete previous document files from '
                                    .storage_path('app/'.$this->filesPath).'?', true)) {
            DB::table('documents')->delete();
            Storage::deleteDirectory($this->filesPath, true);
        }
        Storage::makeDirectory($this->filesPath);

        // Disclaimer: I'm using faker here because Model classes are developed by students
        $faker = Faker\Factory::create('pt_PT');

        $movements = DB::table('movements')->get();
        $files = collect(File::files(database_path('seeds/samples')));

        $selected = $movements->random((int)($movements->count() * $this->movementsProportion));
        foreach ($selected as $movement) {
            $id = DB::table('documents')->insertGetId($this->fakeDocument($faker, $movement, $files));
            DB::table('movements')
                ->where('id', $movement->id)
                ->update(['document_id' => $id]);
        }
    }

    private function fakeDocument(Faker\Generator $faker, $movement, $files)
    {
        $created_at =
            Carbon::createFromFormat('Y-m-d H:i:s', $movement->created_at)
            ->addMinutes($faker->numberBetween(0, 60));
        $file = $files->random();

        $targetDir = $this->filesPath.'/'.$movement->account_id;
        // Document file will user movement id as unique name
        $targetPath = storage_path('app/'.$targetDir.'/'.$movement->id.'.'.$file->getExtension());
        Storage::makeDirectory($targetDir);
        File::copy($file->getPathname(), $targetPath);
        return [
            'type' => $file->getExtension(),
            'original_name' => $file->getBasename(),
            'description' => $faker->randomElement([null, $faker->realText($faker->numberBetween(10, 25))]),
            'created_at' => $created_at
        ];
    }
}
