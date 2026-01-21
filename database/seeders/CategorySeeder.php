<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertamos las mismas categorías que tienes en el Frontend
        \App\Models\Category::create(['id' => 1, 'name' => 'Verduras']);
        \App\Models\Category::create(['id' => 2, 'name' => 'Frutas']);
        \App\Models\Category::create(['id' => 3, 'name' => 'Tubérculos']);
    }
}
