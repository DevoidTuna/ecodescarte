<?php

namespace App\Domain\CollectionPoint\Exception;

use RuntimeException;

final class CollectionPointNotFound extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Collection point {$id} not found.");
    }
}
