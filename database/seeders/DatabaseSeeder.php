<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $workspace = Workspace::query()->updateOrCreate(
            ['slug' => 'rescue-lab'],
            ['name' => 'Rescue Lab', 'timezone' => 'America/New_York', 'currency' => 'USD'],
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'operator@rescuelab.test'],
            [
                'workspace_id' => $workspace->id,
                'name' => 'Alex Morgan',
                'role' => 'Revenue Operations',
                'password' => Hash::make('rescue-demo'),
                'email_verified_at' => now(),
            ],
        );

        resolve(DemoScenarioSeeder::class)->seed($workspace);
    }
}
