<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ufs = [
            ['descricao' => 'Grãos e Cereais','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Massas','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Pães e Produtos de Panificação','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Produtos lácteos','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Carnes e Substitutos','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Frutas e Vegetais','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Produtos Enlatados','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Produtos Congelados','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Lanches','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Produtos de Confeitaria','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Bebidas','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
            ['descricao' => 'Condimentos e Molhos','user_id' => 1,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")],
        ];
    
        DB::table('categorias')->insert($ufs);

    }
}