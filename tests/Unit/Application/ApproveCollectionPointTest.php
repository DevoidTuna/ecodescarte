<?php

namespace Tests\Unit\Application;

use App\Application\CollectionPoint\ApproveCollectionPoint;
use App\Application\CollectionPoint\ListPendingSubmissions;
use App\Application\CollectionPoint\ListPublishedPoints;
use App\Application\CollectionPoint\SubmitCollectionPoint;
use App\Application\CollectionPoint\SubmitCollectionPointInput;
use App\Domain\CollectionPoint\Exception\CollectionPointNotFound;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryCollectionPointRepository;

class ApproveCollectionPointTest extends TestCase
{
    private InMemoryCollectionPointRepository $points;

    protected function setUp(): void
    {
        $this->points = new InMemoryCollectionPointRepository();
    }

    private function submitPoint(string $name): int
    {
        $submit = new SubmitCollectionPoint($this->points);

        return $submit(SubmitCollectionPointInput::fromValidated([
            'name' => $name,
            'address' => 'Rua das Flores, 100',
            'latitude' => -26.9077,
            'longitude' => -48.6619,
            'waste_types' => ['medicamentos'],
        ]))->id();
    }

    public function test_approving_moves_the_point_from_the_queue_to_the_map(): void
    {
        $approve = new ApproveCollectionPoint($this->points);
        $listPublished = new ListPublishedPoints($this->points);
        $listPending = new ListPendingSubmissions($this->points);

        $id = $this->submitPoint('Ecoponto Novo');

        $this->assertCount(1, $listPending());
        $this->assertCount(0, $listPublished());

        $approved = $approve($id);

        $this->assertTrue($approved->isPublished());
        $this->assertCount(0, $listPending());
        $this->assertCount(1, $listPublished());
        $this->assertSame('Ecoponto Novo', $listPublished()[0]->name());
    }

    public function test_the_approval_survives_a_reload(): void
    {
        $approve = new ApproveCollectionPoint($this->points);
        $id = $this->submitPoint('Ecoponto Novo');

        $approve($id);

        $this->assertTrue($this->points->ofId($id)?->isPublished());
    }

    public function test_it_fails_loudly_for_a_point_that_does_not_exist(): void
    {
        $approve = new ApproveCollectionPoint($this->points);

        $this->expectException(CollectionPointNotFound::class);

        $approve(404);
    }
}
