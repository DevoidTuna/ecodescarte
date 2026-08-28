<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Persistence model for the collection_points table.
 *
 * In the moderation flow it is used only by the infrastructure repository,
 * which translates it into the App\Domain\CollectionPoint entity. The canonical
 * list of waste types lives in the WasteType enum, not here any more.
 */
class CollectionPoint extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionPointFactory> */
    use HasFactory;

    /**
     * Mass-assignable fields.
     */
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'waste_types',
        'contact_phone',
        'contact_email',
        'status',
    ];

    /**
     * Column casts.
     */
    protected function casts(): array
    {
        return [
            'waste_types' => 'array',   // json column <-> PHP array
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
