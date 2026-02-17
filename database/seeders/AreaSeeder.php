<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = ['horno', 'parrilla', 'cocina', 'bar'];

        foreach ($areas as $name) {
            Area::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => Str::title($name),
                    'active' => true,
                ]
            );
        }
    }
}
