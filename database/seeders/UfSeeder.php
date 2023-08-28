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
            ['uf' => 'KG','user_id' => 1],
            ['uf' => 'LT','user_id' => 1],
            ['uf' => 'LI','user_id' => 1],
            ['uf' => 'PCT','user_id' => 1],
            ['uf' => 'CX','user_id' => 1],
            ['uf' => 'UN','user_id' => 1],
            ['uf' => 'DZ','user_id' => 1],
            ['uf' => 'BD','user_id' => 1],
            ['uf' => 'SC','user_id' => 1],
        ];
    
        DB::table('ufs')->insert($ufs);
    }
}
