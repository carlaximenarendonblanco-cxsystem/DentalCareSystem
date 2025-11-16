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
            ['email' => 'superadmin@dentalcare.com'],
            [
                'name' => 'Super Admin',
                'ci' => '8671485',
                'password' => Hash::make('8671485'),
                'role' => 'superadmin',
            ]
        );
        User::updateOrCreate(
            ['email' => 'clinica1@gmail.com'],
            [
                'name' => 'Clinica1',
                'ci' => '123451',
                'password' => Hash::make('123451'),
                'role' => 'admin',
            ]
        );
        User::updateOrCreate(
            ['email' => 'clinica2@gmail.com'],
            [
                'name' => 'Clinica2',
                'ci' => '123452',
                'password' => Hash::make('123452'),
                'role' => 'admin',
            ]
        );
    }
}
