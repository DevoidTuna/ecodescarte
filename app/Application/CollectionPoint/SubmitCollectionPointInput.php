<?php

namespace App\Application\CollectionPoint;

use App\Domain\CollectionPoint\WasteType;

/**
 * The use case input, already in domain types. Note the absence of a status:
 * whoever submits a point has no way to ask for it to be born approved.
 */
final readonly class SubmitCollectionPointInput
{
    /**
     * @param  list<WasteType>  $wasteTypes
     */
    public function __construct(
        public string $name,
        public string $address,
        public float $latitude,
        public float $longitude,
        public array $wasteTypes,
        public ?string $contactPhone = null,
        public ?string $contactEmail = null,
    ) {
    }

    /**
     * Converts a payload already validated at the edge — HTTP, console command
     * or queue — into domain types. Unknown keys are ignored.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            address: (string) $data['address'],
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            wasteTypes: array_map(
                static fn (string $type) => WasteType::from($type),
                array_values($data['waste_types']),
            ),
            contactPhone: $data['contact_phone'] ?? null,
            contactEmail: $data['contact_email'] ?? null,
        );
    }
}
