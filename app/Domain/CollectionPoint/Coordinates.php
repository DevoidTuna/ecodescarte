<?php

namespace App\Domain\CollectionPoint;

use App\Domain\CollectionPoint\Exception\InvalidCoordinates;

/**
 * A geographic coordinate pair. A value object: an invalid instance cannot
 * exist, so no layer above has to re-check the range.
 */
final readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
        if ($latitude < -90 || $latitude > 90) {
            throw InvalidCoordinates::latitude($latitude);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw InvalidCoordinates::longitude($longitude);
        }
    }
}
