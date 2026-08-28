<?php

namespace App\Application\CollectionPoint;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;

/**
 * Use case: the team's moderation queue.
 */
final readonly class ListPendingSubmissions
{
    public function __construct(private CollectionPointRepository $points)
    {
    }

    /**
     * @return list<CollectionPoint>
     */
    public function __invoke(): array
    {
        return $this->points->awaitingModeration();
    }
}
