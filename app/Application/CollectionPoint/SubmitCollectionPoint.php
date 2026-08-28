<?php

namespace App\Application\CollectionPoint;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;
use App\Domain\CollectionPoint\Coordinates;

/**
 * Use case: the public submits a collection point.
 */
final readonly class SubmitCollectionPoint
{
    public function __construct(private CollectionPointRepository $points)
    {
    }

    public function __invoke(SubmitCollectionPointInput $input): CollectionPoint
    {
        $point = CollectionPoint::submit(
            name: $input->name,
            address: $input->address,
            coordinates: new Coordinates($input->latitude, $input->longitude),
            wasteTypes: $input->wasteTypes,
            contactPhone: $input->contactPhone,
            contactEmail: $input->contactEmail,
        );

        return $this->points->save($point);
    }
}
