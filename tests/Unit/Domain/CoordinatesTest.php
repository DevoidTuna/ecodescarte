<?php

namespace Tests\Unit\Domain;

use App\Domain\CollectionPoint\Coordinates;
use App\Domain\CollectionPoint\Exception\InvalidCoordinates;
use PHPUnit\Framework\TestCase;

/**
 * No database and no framework: it extends PHPUnit's TestCase, not Laravel's.
 */
class CoordinatesTest extends TestCase
{
    public function test_it_holds_a_pair_inside_the_valid_range(): void
    {
        $coordinates = new Coordinates(-26.9077, -48.6619);

        $this->assertSame(-26.9077, $coordinates->latitude);
        $this->assertSame(-48.6619, $coordinates->longitude);
    }

    public function test_it_accepts_the_exact_boundaries(): void
    {
        $this->assertSame(90.0, (new Coordinates(90, 180))->latitude);
        $this->assertSame(-180.0, (new Coordinates(-90, -180))->longitude);
    }

    public function test_it_rejects_a_latitude_beyond_the_poles(): void
    {
        $this->expectException(InvalidCoordinates::class);

        new Coordinates(120, -48.6619);
    }

    public function test_it_rejects_a_longitude_off_the_map(): void
    {
        $this->expectException(InvalidCoordinates::class);

        new Coordinates(-26.9077, 200);
    }
}
