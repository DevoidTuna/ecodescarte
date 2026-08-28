<?php

namespace App\Domain\CollectionPoint\Exception;

use InvalidArgumentException;

final class InvalidCoordinates extends InvalidArgumentException
{
    public static function latitude(float $value): self
    {
        return new self("Latitude {$value} is outside the -90..90 range.");
    }

    public static function longitude(float $value): self
    {
        return new self("Longitude {$value} is outside the -180..180 range.");
    }
}
