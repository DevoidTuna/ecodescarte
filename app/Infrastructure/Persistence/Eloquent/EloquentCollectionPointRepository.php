<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\CollectionPointRepository;
use App\Domain\CollectionPoint\Coordinates;
use App\Domain\CollectionPoint\ModerationStatus;
use App\Domain\CollectionPoint\WasteType;
use App\Models\CollectionPoint as CollectionPointModel;

/**
 * The outbound adapter: it translates between the domain entity and the table.
 * It is the only part of the moderation flow that knows about Eloquent.
 */
final class EloquentCollectionPointRepository implements CollectionPointRepository
{
    public function published(): array
    {
        return $this->toDomainList(
            CollectionPointModel::query()
                ->where('status', ModerationStatus::Approved->value)
                ->orderBy('name')
                ->get()
                ->all(),
        );
    }

    public function awaitingModeration(): array
    {
        return $this->toDomainList(
            CollectionPointModel::query()
                ->where('status', ModerationStatus::Pending->value)
                ->orderBy('created_at')
                ->get()
                ->all(),
        );
    }

    public function ofId(int $id): ?CollectionPoint
    {
        $model = CollectionPointModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function save(CollectionPoint $point): CollectionPoint
    {
        $model = $point->id() === null
            ? new CollectionPointModel()
            : CollectionPointModel::query()->findOrFail($point->id());

        $model->name = $point->name();
        $model->address = $point->address();
        $model->latitude = $point->coordinates()->latitude;
        $model->longitude = $point->coordinates()->longitude;
        $model->waste_types = array_map(
            static fn (WasteType $type) => $type->value,
            $point->wasteTypes(),
        );
        $model->contact_phone = $point->contactPhone();
        $model->contact_email = $point->contactEmail();
        $model->status = $point->status()->value;

        $model->save();

        return $this->toDomain($model);
    }

    /**
     * @param  list<CollectionPointModel>  $models
     * @return list<CollectionPoint>
     */
    private function toDomainList(array $models): array
    {
        return array_map($this->toDomain(...), $models);
    }

    private function toDomain(CollectionPointModel $model): CollectionPoint
    {
        return CollectionPoint::restore(
            id: $model->id,
            name: $model->name,
            address: $model->address,
            coordinates: new Coordinates((float) $model->latitude, (float) $model->longitude),
            wasteTypes: $this->toWasteTypes($model->waste_types ?? []),
            contactPhone: $model->contact_phone,
            contactEmail: $model->contact_email,
            status: ModerationStatus::from($model->status),
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
        );
    }

    /**
     * Silently drops a term that no longer exists in the domain: an old row
     * carrying a retired waste type should still show up on the map rather
     * than bring the whole listing down.
     *
     * @param  list<string>  $stored
     * @return list<WasteType>
     */
    private function toWasteTypes(array $stored): array
    {
        return array_values(array_filter(
            array_map(static fn (string $type) => WasteType::tryFrom($type), $stored),
        ));
    }
}
