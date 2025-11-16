<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Clinic::updateOrCreate(
            ['name' => 'Clinica1'],
            [
                'address' => 'Av. Principal #123, Ciudad',
                'phone' => '12345678',
            ]
        );

        Clinic::updateOrCreate(
            ['name' => 'Clinica2'],
            [
                'address' => 'Av. Norte #456, Ciudad',
                'phone' => '87654321',
            ]
        );
    }
}
