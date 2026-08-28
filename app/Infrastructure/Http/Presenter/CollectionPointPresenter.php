<?php

namespace App\Infrastructure\Http\Presenter;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\WasteType;
use DateTimeImmutable;
use DateTimeZone;

/**
 * The outbound HTTP adapter: it decides how an entity becomes JSON.
 *
 * It exists so the domain does not need a toArray() shaped around whatever the
 * SPA expects — that shape is the edge's concern, not the business rule's.
 */
final class CollectionPointPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(CollectionPoint $point): array
    {
        return [
            'id' => $point->id(),
            'name' => $point->name(),
            'address' => $point->address(),
            'latitude' => $point->coordinates()->latitude,
            'longitude' => $point->coordinates()->longitude,
            'waste_types' => array_map(
                static fn (WasteType $type) => $type->value,
                $point->wasteTypes(),
            ),
            'contact_phone' => $point->contactPhone(),
            'contact_email' => $point->contactEmail(),
            'status' => $point->status()->value,
            'created_at' => self::timestamp($point->createdAt()),
            'updated_at' => self::timestamp($point->updatedAt()),
        ];
    }

    /**
     * @param  list<CollectionPoint>  $points
     * @return list<array<string, mixed>>
     */
    public static function collection(array $points): array
    {
        return array_map(self::toArray(...), $points);
    }

    /**
     * The same format Eloquent uses when serialising a datetime, so the
     * response stays identical to what it was before the refactor.
     */
    private static function timestamp(?DateTimeImmutable $moment): ?string
    {
        return $moment?->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
