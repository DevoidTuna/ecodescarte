<?php

namespace Database\Factories;

use App\Models\CollectionPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionPoint>
 */
class CollectionPointFactory extends Factory
{
    protected $model = CollectionPoint::class;

    /**
     * Ponto de coleta em coordenadas plausíveis para o Brasil.
     * Nasce pendente, que é como o fluxo real cria os pontos.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'latitude' => fake()->latitude(-33, 5),
            'longitude' => fake()->longitude(-73, -34),
            'waste_types' => fake()->randomElements(CollectionPoint::WASTE_TYPES, 2),
            'contact_phone' => fake()->numerify('(47) 9####-####'),
            'contact_email' => fake()->safeEmail(),
            'status' => 'pending',
        ];
    }

    /**
     * Ponto já aprovado pela equipe, visível no mapa público.
     */
    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}
