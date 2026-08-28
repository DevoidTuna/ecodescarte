<?php

namespace Database\Factories;

use App\Domain\CollectionPoint\WasteType;
use App\Models\CollectionPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionPoint>
 */
class CollectionPointFactory extends Factory
{
    protected $model = CollectionPoint::class;

    /**
     * A collection point at coordinates plausible for Brazil.
     * It is born pending, which is how the real flow creates points.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'latitude' => fake()->latitude(-33, 5),
            'longitude' => fake()->longitude(-73, -34),
            'waste_types' => fake()->randomElements(WasteType::values(), 2),
            'contact_phone' => fake()->numerify('(47) 9####-####'),
            'contact_email' => fake()->safeEmail(),
            'status' => 'pending',
        ];
    }

    /**
     * A point already approved by the team, visible on the public map.
     */
    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}
