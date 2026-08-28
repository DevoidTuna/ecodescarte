<?php

namespace Tests\Support;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;
use DateTimeImmutable;

/**
 * The second adapter for the persistence port: an array.
 *
 * It is what makes it possible to exercise the use cases with no database, no
 * migrations and no framework boot — the tests that use it run in
 * milliseconds.
 */
final class InMemoryCollectionPointRepository implements CollectionPointRepository
{
    /** @var array<int, CollectionPoint> */
    private array $points = [];

    private int $nextId = 1;

    public function published(): array
    {
        $published = array_values(array_filter(
            $this->points,
            static fn (CollectionPoint $point) => $point->isPublished(),
        ));

        usort($published, static fn (CollectionPoint $a, CollectionPoint $b) => $a->name() <=> $b->name());

        return $published;
    }

    public function awaitingModeration(): array
    {
        $pending = array_values(array_filter(
            $this->points,
            static fn (CollectionPoint $point) => $point->isAwaitingModeration(),
        ));

        usort($pending, static fn (CollectionPoint $a, CollectionPoint $b) => $a->createdAt() <=> $b->createdAt());

        return $pending;
    }

    public function ofId(int $id): ?CollectionPoint
    {
        return $this->points[$id] ?? null;
    }

    public function save(CollectionPoint $point): CollectionPoint
    {
        $id = $point->id() ?? $this->nextId++;
        $now = new DateTimeImmutable();

        // The same promise the Eloquent adapter makes: return the entity as it
        // was stored, with id and timestamps assigned.
        $stored = CollectionPoint::restore(
            id: $id,
            name: $point->name(),
            address: $point->address(),
            coordinates: $point->coordinates(),
            wasteTypes: $point->wasteTypes(),
            contactPhone: $point->contactPhone(),
            contactEmail: $point->contactEmail(),
            status: $point->status(),
            createdAt: $point->createdAt() ?? $now,
            updatedAt: $now,
        );

        $this->points[$id] = $stored;

        return $stored;
    }
}
