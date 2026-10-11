<?php

namespace Database\Seeders;

use App\Models\BookingType;
use Illuminate\Database\Seeder;

class BookingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Session',         'slug' => 'session',          'color' => 'brand',  'display_order' => 1, 'description' => 'A standard gym session booking.'],
            ['name' => 'Personal Training', 'slug' => 'personal_training', 'color' => 'orange', 'display_order' => 2, 'description' => 'One-on-one session with a personal trainer.'],
            ['name' => 'Group Class',     'slug' => 'group_class',      'color' => 'green',  'display_order' => 3, 'description' => 'A scheduled group fitness class.'],
            ['name' => 'Consultation',    'slug' => 'consultation',     'color' => 'blue',   'display_order' => 4, 'description' => 'Fitness or nutrition consultation.'],
        ];

        foreach ($types as $t) {
            BookingType::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'name'          => $t['name'],
                    'color'         => $t['color'],
                    'display_order' => $t['display_order'],
                    'description'   => $t['description'],
                    'is_active'     => true,
                ]
            );
        }

        $this->command?->info('Booking types seeded.');
    }
}
