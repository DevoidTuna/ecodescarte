<?php

namespace Tests\Unit\Application;

use App\Application\CollectionPoint\ListPublishedPoints;
use App\Application\CollectionPoint\SubmitCollectionPoint;
use App\Application\CollectionPoint\SubmitCollectionPointInput;
use App\Domain\CollectionPoint\ModerationStatus;
use App\Domain\CollectionPoint\WasteType;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryCollectionPointRepository;

/**
 * The same behaviour the moderation feature test covers, but with no HTTP and
 * no PostgreSQL: the persistence port is an array.
 */
class SubmitCollectionPointTest extends TestCase
{
    private InMemoryCollectionPointRepository $points;

    protected function setUp(): void
    {
        $this->points = new InMemoryCollectionPointRepository();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Farmácia do Bairro',
            'address' => 'Rua das Flores, 100',
            'latitude' => -26.9077,
            'longitude' => -48.6619,
            'waste_types' => ['medicamentos'],
        ], $overrides);
    }

    public function test_a_submitted_point_is_stored_as_pending(): void
    {
        $submit = new SubmitCollectionPoint($this->points);

        $point = $submit(SubmitCollectionPointInput::fromValidated($this->payload()));

        $this->assertSame(ModerationStatus::Pending, $point->status());
        $this->assertSame('Farmácia do Bairro', $point->name());
        $this->assertSame([WasteType::Medicamentos], $point->wasteTypes());
    }

    public function test_the_payload_cannot_ask_for_an_approved_point(): void
    {
        // Even carrying status=approved, the field never reaches the domain.
        $submit = new SubmitCollectionPoint($this->points);

        $point = $submit(SubmitCollectionPointInput::fromValidated(
            $this->payload(['status' => 'approved']),
        ));

        $this->assertSame(ModerationStatus::Pending, $point->status());
    }

    public function test_a_submitted_point_does_not_reach_the_public_map(): void
    {
        $submit = new SubmitCollectionPoint($this->points);
        $listPublished = new ListPublishedPoints($this->points);

        $submit(SubmitCollectionPointInput::fromValidated($this->payload()));

        $this->assertSame([], $listPublished());
    }

    public function test_persisting_assigns_an_id(): void
    {
        $submit = new SubmitCollectionPoint($this->points);

        $point = $submit(SubmitCollectionPointInput::fromValidated($this->payload()));

        $this->assertNotNull($point->id());
        $this->assertSame($point->id(), $this->points->ofId($point->id())?->id());
    }
}
