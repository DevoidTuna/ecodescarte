<?php

namespace App\Application\CollectionPoint;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;

/**
 * Use case: what the public map sees.
 */
final readonly class ListPublishedPoints
{
    public function __construct(private CollectionPointRepository $points)
    {
    }

    /**
     * @return list<CollectionPoint>
     */
    public function __invoke(): array
    {
        return $this->points->published();
    }
}
