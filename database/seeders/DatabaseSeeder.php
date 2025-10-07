<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // Seed in correct order (dependencies)
        $this->call([
            // 1. Users first (needed for created_by references)
            UserSeeder::class,

            // 2. Company structure
            CompanyGroupSeeder::class,
            CompanyBranchSeeder::class,

            // 3. Devices (depends on branches)
            DeviceMasterSeeder::class,

            // 4. Event settings (depends on branches and devices)
            BranchEventSettingSeeder::class,

            // 5. CCTV layouts (depends on users, branches, devices)
            CctvLayoutSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
