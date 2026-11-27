<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'Elevator' => 'Utilities',
            'Generator' => 'Utilities',
            'CCTV' => 'Safety',
            'Fire Safety System' => 'Safety',
            'Rooftop Garden' => 'Recreation',
            'Gym' => 'Recreation',
            'Prayer Room' => 'Services',
            'Parking' => 'Parking',
            'Solar System' => 'Utilities',
            'Swimming Pool' => 'Recreation',
            'Community Hall' => 'Recreation',
            'Water Supply System' => 'Utilities',
        ];

        foreach ($amenities as $name => $category) {
            Amenity::firstOrCreate(
                ['name' => $name],
                [
                    'category' => $category,
                    'status' => 'active',
                ]
            );
        }
    }
}
