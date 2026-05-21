<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = \App\Models\Nivel::where('nombre', 'Administrador')->first();

        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@vetmora.com',
            'password' => 'password', // Will be hashed by model cast
            'nivel_id' => $adminRole->id,
        ]);
    }
}
