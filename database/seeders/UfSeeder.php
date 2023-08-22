<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ufs = [
            ['uf' => 'KG'],
            ['uf' => 'LT'],
            ['uf' => 'LI'],
            ['uf' => 'PCT'],
            ['uf' => 'CX'],
            ['uf' => 'UN'],
            ['uf' => 'DZ'],
            ['uf' => 'BD'],
            ['uf' => 'SC'],
        ];
    
        DB::table('ufs')->insert($ufs);
    }
}
