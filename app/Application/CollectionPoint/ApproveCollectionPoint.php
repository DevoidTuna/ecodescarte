<?php

namespace App\Application\CollectionPoint;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;
use App\Domain\CollectionPoint\Exception\CollectionPointNotFound;

/**
 * Use case: the team publishes a pending point on the map.
 */
final readonly class ApproveCollectionPoint
{
    public function __construct(private CollectionPointRepository $points)
    {
    }

    public function __invoke(int $id): CollectionPoint
    {
        $point = $this->points->ofId($id);

        if ($point === null) {
            throw CollectionPointNotFound::withId($id);
        }

        $point->approve();

        return $this->points->save($point);
    }
}
