<?php

namespace Tests\Unit\Domain;

use App\Domain\CollectionPoint\CollectionPoint;
use App\Domain\CollectionPoint\Coordinates;
use App\Domain\CollectionPoint\Exception\InvalidSubmission;
use App\Domain\CollectionPoint\ModerationStatus;
use App\Domain\CollectionPoint\WasteType;
use PHPUnit\Framework\TestCase;

/**
 * The central rule of the application, exercised with no database, no HTTP and
 * no Laravel.
 */
class CollectionPointTest extends TestCase
{
    private function submit(array $overrides = []): CollectionPoint
    {
        return CollectionPoint::submit(
            name: $overrides['name'] ?? 'Farmácia do Bairro',
            address: $overrides['address'] ?? 'Rua das Flores, 100',
            coordinates: new Coordinates(-26.9077, -48.6619),
            wasteTypes: $overrides['wasteTypes'] ?? [WasteType::Medicamentos],
        );
    }

    public function test_a_submitted_point_is_always_pending(): void
    {
        // submit() has no status parameter: the entry channel cannot ask for
        // an approved point even if it wants to.
        $point = $this->submit();

        $this->assertSame(ModerationStatus::Pending, $point->status());
        $this->assertTrue($point->isAwaitingModeration());
        $this->assertFalse($point->isPublished());
    }

    public function test_a_submitted_point_has_no_id_until_it_is_persisted(): void
    {
        $this->assertNull($this->submit()->id());
    }

    public function test_approving_publishes_the_point(): void
    {
        $point = $this->submit();

        $point->approve();

        $this->assertTrue($point->isPublished());
        $this->assertSame(ModerationStatus::Approved, $point->status());
    }

    public function test_approving_twice_is_not_an_error(): void
    {
        // Two moderators can have the same queue open; the second approval
        // must not blow up.
        $point = $this->submit();

        $point->approve();
        $point->approve();

        $this->assertTrue($point->isPublished());
    }

    public function test_it_rejects_a_submission_without_a_name(): void
    {
        $this->expectException(InvalidSubmission::class);

        $this->submit(['name' => '   ']);
    }

    public function test_it_rejects_a_submission_without_an_address(): void
    {
        $this->expectException(InvalidSubmission::class);

        $this->submit(['address' => '']);
    }

    public function test_it_rejects_a_submission_that_accepts_no_waste(): void
    {
        $this->expectException(InvalidSubmission::class);

        $this->submit(['wasteTypes' => []]);
    }
}
