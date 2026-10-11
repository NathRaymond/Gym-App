<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Feedback;
use App\Models\GymClass;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
            BookingTypeSeeder::class,
        ]);

        // ============================================
        // USERS — one per role (idempotent)
        // ============================================

        // 1. SUPER ADMIN
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gymcenter.com'],
            [
                'first_name' => 'Super',
                'last_name'  => 'Demanchy',
                'phone'      => '+2348000000001',
                'password'   => bcrypt('password'),
                'email_verified_at' => now(),
                'status'     => 'active',
                'is_verified' => true,
                'membership_number' => 'GYM-ADMIN-001',
            ]
        );
        $superAdmin->assignRole('super-admin');
        $superAdmin->getOrCreateWallet();

        // ============================================
        // MEMBERSHIP PLANS
        // ============================================
        $plans = [
            [
                'name' => 'Day Pass', 'price' => 5000, 'duration_days' => 1, 'duration_type' => 'daily',
                'sessions_included' => 1, 'features' => ['Access to gym floor', 'Locker use'],
                'display_order' => 1, 'is_active' => true,
            ],
            [
                'name' => 'Monthly Standard', 'price' => 35000, 'duration_days' => 30, 'duration_type' => 'monthly',
                'sessions_included' => null, 'features' => ['Unlimited gym access', 'Group classes', 'Locker'],
                'display_order' => 2, 'is_active' => true, 'is_featured' => true,
            ],
            [
                'name' => 'Quarterly Premium', 'price' => 95000, 'duration_days' => 90, 'duration_type' => 'quarterly',
                'sessions_included' => null, 'has_pt_sessions' => true, 'pt_sessions_count' => 4,
                'features' => ['Unlimited access', 'Group classes', '4 PT sessions', 'Sauna', 'Nutrition plan'],
                'display_order' => 3, 'is_active' => true,
            ],
            [
                'name' => 'Yearly Elite', 'price' => 350000, 'duration_days' => 365, 'duration_type' => 'yearly',
                'sessions_included' => null, 'has_pt_sessions' => true, 'pt_sessions_count' => 24,
                'features' => ['All access', 'Unlimited classes', '24 PT sessions', 'Spa', 'Personal locker', 'Guest passes'],
                'display_order' => 4, 'is_active' => true,
            ],
        ];
        foreach ($plans as $plan) {
            MembershipPlan::updateOrCreate(['name' => $plan['name']], $plan);
        }

        // ============================================
        // GYM CLASSES
        // ============================================
        foreach ([
            ['Yoga Flow', 'all_levels'],
            ['Spin Cycle', 'intermediate'],
            ['CrossFit Blast', 'advanced'],
            ['Zumba Dance', 'all_levels'],
        ] as $c) {
            GymClass::updateOrCreate(
                ['slug' => \Str::slug($c[0])],
                [
                    'name'        => $c[0],
                    'description' => 'Join our ' . $c[0] . ' class.',
                    'capacity'    => 20,
                    'difficulty'  => $c[1],
                    'price'       => 0,
                    'is_active'   => true,
                ]
            );
        }

        // ============================================
        // ANNOUNCEMENT
        // ============================================
        Announcement::firstOrCreate(
            ['title' => 'Welcome to GymCenter Ibadan!'],
            [
                'content'   => 'We are excited to have you. Check out our new classes and membership plans.',
                'audience'  => 'all',
                'is_active' => true,
                'created_by' => $superAdmin->id,
                'starts_at' => now(),
            ]
        );

        // ============================================
        // SAMPLE FEEDBACK
        // ============================================
        Feedback::firstOrCreate(
            ['user_id' => $member->id, 'title' => 'Best gym in Ibadan!'],
            [
                'rating' => 5,
                'message' => 'The trainers are amazing and the equipment is top-notch. Highly recommend!',
                'is_approved' => true,
                'is_featured' => true,
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
            ]
        );

        Feedback::firstOrCreate(
            ['user_id' => $member2->id, 'title' => 'Great experience'],
            [
                'rating' => 4,
                'message' => 'Love the yoga classes. The facility is clean and modern.',
                'is_approved' => true,
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
            ]
        );

        // ============================================
        // DONE
        // ============================================
        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📋 LOGIN CREDENTIALS:');
        $this->command->info('   ┌─────────────────────────────────────────────────────┐');
        $this->command->info('   │ Super Admin │ superadmin@gymcenter.com │ password │');
        $this->command->info('   │ Admin       │ ade@gymcenter.com        │ password │');
        $this->command->info('   │ Manager     │ ngozi@gymcenter.com      │ password │');
        $this->command->info('   │ Front Desk  │ chidi@gymcenter.com      │ password │');
        $this->command->info('   │ Trainer     │ emeka@gymcenter.com      │ password │');
        $this->command->info('   │ Member      │ tunde@gymcenter.com      │ password │');
        $this->command->info('   │ Member 2    │ amaka@gymcenter.com      │ password │');
        $this->command->info('   └─────────────────────────────────────────────────────┘');
        $this->command->info('');
    }
}