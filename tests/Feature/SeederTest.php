<?php

namespace Tests\Feature;

use App\Models\CollectionPoint;
use App\Models\User;
use Database\Seeders\CollectionPointSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The container entrypoint seeds on every boot, so re-running the seeder has
 * to be harmless. These tests pin that guarantee down.
 */
class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_twice_does_not_duplicate_collection_points(): void
    {
        $this->seed(CollectionPointSeeder::class);
        $afterFirstRun = CollectionPoint::count();

        $this->seed(CollectionPointSeeder::class);

        $this->assertGreaterThan(0, $afterFirstRun);
        $this->assertSame($afterFirstRun, CollectionPoint::count());
    }

    public function test_reseeding_does_not_overwrite_points_edited_in_the_admin_area(): void
    {
        $this->seed(CollectionPointSeeder::class);

        $point = CollectionPoint::firstOrFail();
        $point->update(['status' => 'pending', 'contact_phone' => '(47) 3344-5566']);

        $this->seed(CollectionPointSeeder::class);

        $this->assertSame('pending', $point->fresh()->status);
        $this->assertSame('(47) 3344-5566', $point->fresh()->contact_phone);
    }

    public function test_seeding_twice_does_not_duplicate_the_team_user(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('username', 'admin')->count());
    }
}
