<?php

namespace App\Domain\CollectionPoint\Exception;

use InvalidArgumentException;

final class InvalidSubmission extends InvalidArgumentException
{
    public static function blankName(): self
    {
        return new self('A collection point needs a name.');
    }

    public static function blankAddress(): self
    {
        return new self('A collection point needs an address.');
    }

    public static function withoutWasteTypes(): self
    {
        return new self('A collection point must accept at least one waste type.');
    }
}
