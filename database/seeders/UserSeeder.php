<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'carlaximenarb@gmail.com'],
            [
                'name' => 'Carla Rendon',
                'ci' => '8671485',
                'password' => Hash::make('8671485'),
                'role' => 'superadmin',
            ]
        );
    }
}
