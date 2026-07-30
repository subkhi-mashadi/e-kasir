<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ekasir.app'],
            [
                'name'       => 'Super Admin',
                'password'   => 'admin123456',
                'company_id' => null,
                'branch_id'  => null,
                'is_active'  => true,
            ]
        );

        $admin->syncRoles(['super_admin']);

        $this->command->info('Admin seeded: admin@ekasir.app / admin123456 (akses /admin)');
    }
}
