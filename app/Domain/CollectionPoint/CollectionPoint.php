<?php

namespace App\Domain\CollectionPoint;

use App\Domain\CollectionPoint\Exception\InvalidSubmission;
use DateTimeImmutable;

/**
 * A collection point as a business rule, with no Eloquent and no Laravel.
 *
 * The central rule of the application lives here: a submission from the public
 * is born pending, and only the team can publish it. The entry channel — HTTP,
 * console or queue — has no way to choose the status, not even by forging the
 * field in the request.
 */
final class CollectionPoint
{
    /**
     * @param  list<WasteType>  $wasteTypes
     */
    private function __construct(
        private ?int $id,
        private string $name,
        private string $address,
        private Coordinates $coordinates,
        private array $wasteTypes,
        private ?string $contactPhone,
        private ?string $contactEmail,
        private ModerationStatus $status,
        private ?DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * A submission from the public. Note there is no status parameter.
     *
     * @param  list<WasteType>  $wasteTypes
     */
    public static function submit(
        string $name,
        string $address,
        Coordinates $coordinates,
        array $wasteTypes,
        ?string $contactPhone = null,
        ?string $contactEmail = null,
    ): self {
        if (trim($name) === '') {
            throw InvalidSubmission::blankName();
        }

        if (trim($address) === '') {
            throw InvalidSubmission::blankAddress();
        }

        if ($wasteTypes === []) {
            throw InvalidSubmission::withoutWasteTypes();
        }

        return new self(
            id: null,
            name: $name,
            address: $address,
            coordinates: $coordinates,
            wasteTypes: array_values($wasteTypes),
            contactPhone: $contactPhone,
            contactEmail: $contactEmail,
            status: ModerationStatus::Pending,
            createdAt: null,
            updatedAt: null,
        );
    }

    /**
     * Rehydrates a point that is already persisted.
     *
     * It deliberately does not repeat the invariants from submit(): those hold
     * for creation. Re-applying them here would make the application break
     * while reading an old row that no longer satisfies them, rather than
     * simply displaying it.
     *
     * @param  list<WasteType>  $wasteTypes
     */
    public static function restore(
        int $id,
        string $name,
        string $address,
        Coordinates $coordinates,
        array $wasteTypes,
        ?string $contactPhone,
        ?string $contactEmail,
        ModerationStatus $status,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            address: $address,
            coordinates: $coordinates,
            wasteTypes: array_values($wasteTypes),
            contactPhone: $contactPhone,
            contactEmail: $contactEmail,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Publishes the point on the map. Idempotent: approving twice is not an
     * error, because two moderators can have the same queue open.
     */
    public function approve(): void
    {
        $this->status = ModerationStatus::Approved;
    }

    public function isPublished(): bool
    {
        return $this->status === ModerationStatus::Approved;
    }

    public function isAwaitingModeration(): bool
    {
        return $this->status === ModerationStatus::Pending;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function coordinates(): Coordinates
    {
        return $this->coordinates;
    }

    /**
     * @return list<WasteType>
     */
    public function wasteTypes(): array
    {
        return $this->wasteTypes;
    }

    public function contactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function contactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function status(): ModerationStatus
    {
        return $this->status;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
