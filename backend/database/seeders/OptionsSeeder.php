<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'name' => 'service_place_selection_use_map_picker',
                'value' => true,
                'type' => 'boolean'
            ]
        ];

        foreach ($options as $option) {
            $option['default'] = $option['value'];
            \App\Models\Option::create($option);
        }
    }
}
