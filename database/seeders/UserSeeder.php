<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Depot Al Hikmah',
                'email' => 'merchant@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'address' => 'Jl. Raya Dadok No.19, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8824044070053901',
                'long' => '100.36591600326373'
            ],
            [
                'name' => 'Azza Water',
                'email' => 'azzawater@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'address' => 'Jl. Dadok Raya Depan Mesjid Asra, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8833727658459997',
                'long' => '100.36376725081429'
            ],
            [
                'name' => 'Murni Water Cold',
                'email' => 'murniwater@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'address' => 'Jl, Utama Dadok Tunggul Hitam, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8751472707251302',
                'long' => '100.36542225385385'
            ],
            [
                'name' => 'Rahmad Avriantias',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'address' => 'Jl. Raya Dadok No.7, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8821362179080141',
                'long' => '100.36647390271102'
            ],
        ];

        foreach ($user as $user) {
            User::create($user);
        }
    }
}
