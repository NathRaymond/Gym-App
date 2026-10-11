<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Members
            'members.view', 'members.create', 'members.edit', 'members.delete',
            // Memberships
            'memberships.view', 'memberships.create', 'memberships.edit', 'memberships.delete', 'memberships.freeze',
            // Plans
            'plans.view', 'plans.create', 'plans.edit', 'plans.delete',
            // Bookings
            'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete', 'bookings.cancel',
            // Classes
            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            // Trainers
            'trainers.view', 'trainers.create', 'trainers.edit', 'trainers.delete',
            // Payments
            'payments.view', 'payments.create', 'payments.refund',
            // Wallets
            'wallets.view', 'wallets.fund', 'wallets.debit',
            // Tickets
            'tickets.view', 'tickets.verify', 'tickets.cancel',
            // Feedback
            'feedback.view', 'feedback.approve', 'feedback.delete',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.view', 'settings.edit',
            // Roles
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            // Activity
            'activity.view',
            // Announcements
            'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
            // Equipment
            'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::whereNotIn('name', ['roles.delete'])->get());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'users.view','members.view','members.create','members.edit',
            'memberships.view','memberships.create','memberships.edit','memberships.freeze',
            'plans.view','bookings.view','bookings.create','bookings.edit','bookings.cancel',
            'classes.view','classes.create','classes.edit','trainers.view',
            'payments.view','payments.create','wallets.view',
            'tickets.view','tickets.verify','feedback.view','feedback.approve',
            'reports.view','reports.export','announcements.view','announcements.create','announcements.edit',
            'equipment.view','equipment.create','equipment.edit',
        ]);

        $frontDesk = Role::firstOrCreate(['name' => 'front-desk']);
        $frontDesk->syncPermissions([
            'members.view','members.create','memberships.view',
            'bookings.view','bookings.create','bookings.edit',
            'payments.view','payments.create','wallets.view','wallets.fund',
            'tickets.view','tickets.verify','feedback.view',
        ]);

        $trainer = Role::firstOrCreate(['name' => 'trainer']);
        $trainer->syncPermissions([
            'bookings.view','classes.view','members.view','tickets.view',
        ]);

        $member = Role::firstOrCreate(['name' => 'member']);
        $member->syncPermissions([
            'bookings.view','bookings.create','bookings.cancel',
            'classes.view','memberships.view','payments.view','wallets.view','wallets.fund',
        ]);
    }
}