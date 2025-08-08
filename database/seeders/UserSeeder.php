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
                'email' => 'edepot@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'phone' => '083895596096',
                'address' => 'Jl. Raya Dadok No.19, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8824044070053901',
                'long' => '100.36591600326373'
            ],
            [
                'name' => 'Azza Water',
                'email' => 'azzawater@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'phone' => '082288410657',
                'address' => 'Jl. Dadok Raya Depan Mesjid Asra, Dadok Tunggul Hitam, Koto Tangah',
                'lat' => '-0.8833727658459997',
                'long' => '100.36376725081429'
            ],
            [
                'name' => 'Murni Water Cold',
                'email' => 'murniwater@example.com',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'phone' => '085274538471',
                'address' => 'Blk. A No.26, RW.01, Dadok Tunggul Hitam, Kec. Koto Tangah',
                'lat' => '-0.8657184132598755',
                'long' => '100.36521768989117'
            ],
            [
                'name' => 'Rahmad Avriantias',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '082287444224',
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
