<?php

namespace App\Domain\CollectionPoint;

/**
 * Waste types the application accepts.
 *
 * The backing values stay in Portuguese on purpose: they are the canonical
 * representation persisted in the waste_types column and the key the SPA looks
 * up in resources/js/wasteTypes.js to render a label. The case names are code,
 * so they read in English; the values are data, so they do not move.
 */
enum WasteType: string
{
    case Batteries = 'pilhas';
    case CookingOil = 'oleo';
    case Electronics = 'eletronicos';        // electronics and home appliances
    case LightBulbs = 'lampadas';
    case Glass = 'vidro';
    case Plastic = 'plastico';
    case Metal = 'metal';
    case Paper = 'papel';
    case Recyclables = 'reciclaveis';        // dry recyclables
    case Tires = 'pneus';
    case BottleCaps = 'tampinhas';
    case Sponges = 'esponjas';
    case ConstructionDebris = 'entulho';
    case BulkyWaste = 'volumosos';
    case GardenWaste = 'poda';
    case Medication = 'medicamentos';
    case Other = 'outros';                   // reverse logistics / institutional drop-off

    /**
     * The values, for validation at the edges (HTTP, factory, seeder).
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type) => $type->value, self::cases());
    }
}
