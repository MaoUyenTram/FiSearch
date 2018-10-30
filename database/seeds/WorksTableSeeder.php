<?php

use Illuminate\Database\Seeder;

class WorksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('works')->insert([
            'user_name' => 'admin',
            'year' => 2018,
        ]);

        factory(App\Work::class, 5)->create();
    }
}
