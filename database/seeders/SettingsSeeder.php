<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'gym_name', 'value' => 'De-Manchy Ibadan', 'group' => 'general'],
            ['key' => 'gym_email', 'value' => 'info@gymcenter.com', 'group' => 'general'],
            ['key' => 'gym_phone', 'value' => '+234 800 000 0000', 'group' => 'general'],
            ['key' => 'gym_address', 'value' => 'No 19 Oyegbami street felele barraks, Ibadan', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'NGN', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '₦', 'group' => 'general'],
            ['key' => 'wallet_min_funding', 'value' => '1000', 'group' => 'wallet'],
            ['key' => 'booking_cancel_hours', 'value' => '24', 'group' => 'booking'],
            ['key' => 'paystack_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'flutterwave_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'cash_enabled', 'value' => '1', 'group' => 'payment'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}