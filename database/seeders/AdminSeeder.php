<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->truncate();
        
        DB::table('admins')->insert([
            'name' => 'Admin',
            'email' => 'super@gmail.com',
            'phone'=>'0911122360',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
    }
}
