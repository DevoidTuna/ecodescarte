<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionPoint extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionPointFactory> */
    use HasFactory;

    /**
     * Tipos de resíduo aceitos pela aplicação (valores canônicos).
     * Os termos livres do CSV são mapeados para estes valores no seeder.
     */
    public const WASTE_TYPES = [
        'pilhas',        // pilhas e baterias
        'oleo',          // óleo de cozinha
        'eletronicos',   // eletrônicos / eletroeletrônicos / eletrodomésticos
        'lampadas',
        'vidro',
        'plastico',
        'metal',
        'papel',
        'reciclaveis',   // recicláveis (secos)
        'pneus',
        'tampinhas',
        'esponjas',
        'entulho',       // RCC / resíduos de construção
        'volumosos',
        'poda',
        'medicamentos',
        'outros',        // logística reversa / ponto de apoio institucional
    ];

    /**
     * Campos preenchíveis em massa.
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
     * Conversão de tipos das colunas.
     */
    protected function casts(): array
    {
        return [
            'waste_types' => 'array',   // coluna json <-> array PHP
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
