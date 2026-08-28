<?php

namespace App\Domain\CollectionPoint;

/**
 * The outbound port: the domain declares what persistence it needs without
 * knowing who answers. In production that is Eloquent; in the unit tests, an
 * array held in memory.
 */
interface CollectionPointRepository
{
    /**
     * Approved points in alphabetical order — what the public map shows.
     *
     * @return list<CollectionPoint>
     */
    public function published(): array;

    /**
     * The moderation queue: pending points, oldest first.
     *
     * @return list<CollectionPoint>
     */
    public function awaitingModeration(): array;

    public function ofId(int $id): ?CollectionPoint;

    /**
     * Persists the point and returns the entity as it was stored, with id and
     * timestamps assigned in the case of an insert.
     */
    public function save(CollectionPoint $point): CollectionPoint;
}
