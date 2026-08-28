<?php

namespace App\Domain\CollectionPoint;

/**
 * Waste types the application accepts (canonical values).
 * The free-form terms coming from the CSV are mapped onto these in the seeder.
 */
enum WasteType: string
{
    case Pilhas = 'pilhas';                 // batteries of any kind
    case Oleo = 'oleo';                     // cooking oil
    case Eletronicos = 'eletronicos';       // electronics / home appliances
    case Lampadas = 'lampadas';
    case Vidro = 'vidro';
    case Plastico = 'plastico';
    case Metal = 'metal';
    case Papel = 'papel';
    case Reciclaveis = 'reciclaveis';       // dry recyclables
    case Pneus = 'pneus';
    case Tampinhas = 'tampinhas';
    case Esponjas = 'esponjas';
    case Entulho = 'entulho';               // construction debris
    case Volumosos = 'volumosos';
    case Poda = 'poda';
    case Medicamentos = 'medicamentos';
    case Outros = 'outros';                 // reverse logistics / institutional drop-off

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
