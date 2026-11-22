<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Akun Default
        User::updateOrCreate(
            ['email' => 'admin@cbt.test'],
            [
                'name' => 'Admin CBT',
                'nis_nip' => '19800101001',
                'role' => 'admin',
                'password' => 'password', // Otomatis di-hash oleh cast 'password' => 'hashed' di User.php
            ]
        );

        User::updateOrCreate(
            ['email' => 'guru@cbt.test'],
            [
                'name' => 'Budi Santoso, S.Pd',
                'nis_nip' => '19850505002',
                'role' => 'guru',
                'password' => 'password',
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa@cbt.test'],
            [
                'name' => 'Ahmad Fauzi',
                'nis_nip' => '2026001',
                'role' => 'siswa',
                'kelas' => 'Kls 7',
                'password' => 'password',
            ]
        );

        // Buat Mata Pelajaran Default
        Subject::firstOrCreate(['name' => 'Pendidikan Pancasila'], ['code' => 'PPKN']);
        Subject::firstOrCreate(['name' => 'Matematika'], ['code' => 'MTK']);
        Subject::firstOrCreate(['name' => 'Bahasa Indonesia'], ['code' => 'BIND']);
    }
}