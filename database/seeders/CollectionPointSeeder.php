<?php

namespace Database\Seeders;

use App\Models\CollectionPoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionPointSeeder extends Seeder
{
    /**
     * Dictionary: free-text term from the CSV (lowercased) => canonical type.
     */
    private const MAPA_TIPOS = [
        'vidro' => 'vidro',
        'plástico' => 'plastico',
        'metal' => 'metal',
        'papel' => 'papel',
        'óleo de cozinha' => 'oleo',
        'eletrônicos' => 'eletronicos',
        'eletroeletrônicos' => 'eletronicos',
        'eletrodomésticos' => 'eletronicos',
        'materiais blindados' => 'eletronicos',
        'esponjas' => 'esponjas',
        'pilhas' => 'pilhas',
        'baterias' => 'pilhas',
        'tampinhas' => 'tampinhas',
        'lâmpadas' => 'lampadas',
        'recicláveis' => 'reciclaveis',
        'recicláveis secos' => 'reciclaveis',
        'rcc' => 'entulho',
        'volumosos' => 'volumosos',
        'poda' => 'poda',
        'pneus' => 'pneus',
        'pneus inservíveis' => 'pneus',
        'referência institucional do programa descarta.í' => 'outros',
        'ponto de apoio / logística reversa' => 'outros',
    ];

    /**
     * Converts the CSV type string ("a; b; c") into the canonical list, with
     * no duplicates. Unknown terms become 'outros'.
     */
    private function mapearTipos(string $raw): array
    {
        $tipos = collect(explode(';', $raw))
            ->map(fn ($t) => Str::of($t)->trim()->lower()->value())
            ->filter()
            ->map(fn ($t) => self::MAPA_TIPOS[$t] ?? 'outros')
            ->unique()
            ->values()
            ->all();

        return $tipos;
    }

    /**
     * Real collection points (all approved) — Itajaí and its region (SC).
     */
    public function run(): void
    {
        $pontos = [
            ['Itajaí', 'Ecoponto do Centreventos', 'Av. Ministro Victor Konder, 303, Centro, Itajaí - SC', -26.916540, -48.652070, 'Vidro; plástico; metal; papel; óleo de cozinha; eletrônicos; esponjas; pilhas; baterias; tampinhas; lâmpadas'],
            ['Itajaí', 'Ecoponto Nossa Senhora das Graças', 'Av. Vereador Abrahão João Francisco (Contorno Sul), Ressacada, Itajaí - SC', -26.910100, -48.670500, 'Vidro; plástico; metal; papel; óleo de cozinha; eletrônicos; esponjas; pilhas; baterias; tampinhas; lâmpadas'],
            ['Itajaí', 'PEV São Vicente', 'Rua Érico Veríssimo, 658, São Vicente, Itajaí - SC', -26.907998, -48.696683, 'Recicláveis; RCC; volumosos; poda; lâmpadas; pilhas'],
            ['Itajaí', 'Câmara Coleta', 'Av. Vereador Abrahão João Francisco, 3825, Ressacada, Itajaí - SC', -26.918600, -48.669440, 'Eletrônicos; óleo de cozinha; pilhas; baterias'],
            ['Itajaí', 'Ecoponto de pneus inservíveis', 'Rua Arlindo Mafra, 156, Itaipava, Itajaí - SC', -26.943400, -48.711105, 'Pneus inservíveis'],
            ['Itajaí', 'INIS / apoio ao descarte', 'Rua XV de Novembro, 378, Centro, Itajaí - SC', -26.916540, -48.652070, 'Referência institucional do programa Descarta.í'],
            ['Balneário Camboriú', 'Ecoponto Rua 2870', 'Rua 2870, Centro, Balneário Camboriú - SC', -26.998840, -48.630580, 'Recicláveis secos; vidro; metal; plástico; papel; pilhas; baterias; lâmpadas; óleo de cozinha'],
            ['Balneário Camboriú', 'Ecoponto Praça da Barra', 'Praça da Barra, Balneário Camboriú - SC', -26.991100, -48.635200, 'Recicláveis secos; vidro; metal; plástico; papel; pilhas; baterias; lâmpadas; óleo de cozinha'],
            ['Balneário Camboriú', 'Ecoponto Praça Higino Pio', 'Praça Higino Pio, Balneário Camboriú - SC', -26.992200, -48.637500, 'Recicláveis secos; vidro; metal; plástico; papel; pilhas; baterias; lâmpadas; óleo de cozinha'],
            ['Balneário Camboriú', 'Ecoponto Av. Palestina x Rua Paraguai', 'Avenida Palestina, esquina com Rua Paraguai, Nações, Balneário Camboriú - SC', -26.978150, -48.646580, 'Recicláveis secos; vidro; metal; plástico; papel; pilhas; baterias; lâmpadas; óleo de cozinha'],
            ['Camboriú', 'Ecoponto Rio Pequeno', 'Rua Rio Pardo, Rio Pequeno, Camboriú - SC', -27.043720, -48.624210, 'Pneus; baterias; eletrodomésticos; eletroeletrônicos; plástico; metal; vidro'],
            ['Camboriú', 'Ecoponto principal', 'Alameda Teólogo João Calvino, 860, Rio Pequeno, Camboriú - SC', -27.043660, -48.624180, 'Eletroeletrônicos; pneus; pilhas; baterias; materiais blindados'],
            ['Camboriú', 'Prefeitura Municipal de Camboriú', 'Rua Getúlio Vargas, 77, Centro, Camboriú - SC', -27.025800, -48.654900, 'Ponto de apoio / logística reversa'],
            ['Navegantes', 'Ecoponto - Secretaria de Obras', 'Rua José Alcebíades Laurentino, 350, Centro, Navegantes - SC', -26.888236, -48.651298, 'Pilhas; baterias; eletroeletrônicos; óleo de cozinha; vidro; papel; plástico; metal'],
            ['Navegantes', 'Ecoponto Gravatá', 'Rua Carlos Boos, 48, Gravatá, Navegantes - SC', -26.834646, -48.636874, 'Pilhas; baterias; eletroeletrônicos; óleo de cozinha; vidro; papel; plástico; metal'],
            ['Penha', 'Águas de Penha - ponto de óleo', 'Avenida Eugênio Krause, 152, Centro, Penha - SC', -26.776200, -48.646000, 'Óleo de cozinha; pilhas; baterias; lâmpadas'],
            ['Penha', 'APAE Penha - ponto de óleo', 'Rua Erechim, 215, Centro, Penha - SC', -26.774900, -48.643700, 'Óleo de cozinha; pilhas; baterias; lâmpadas'],
            ['Itapema', 'Conasa Águas de Itapema', 'Av. Marginal Leste, 5, Centro, Itapema - SC', -27.090700, -48.611900, 'Óleo de cozinha; eletroeletrônicos; lâmpadas; pilhas; baterias'],
            ['Itapema', 'ACI - ponto de eletroeletrônicos e pilhas', 'Rua 106, Centro, Itapema - SC', -27.089900, -48.608500, 'Eletroeletrônicos; pilhas; baterias'],
        ];

        foreach ($pontos as [$cidade, $nome, $endereco, $lat, $lng, $tiposRaw]) {
            // firstOrCreate rather than create: the seeder runs on every
            // container boot, and re-running it must neither duplicate points
            // nor overwrite edits the team made in /admin.
            CollectionPoint::firstOrCreate(
                [
                    'name' => $nome,
                    'address' => $endereco,
                ],
                [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'waste_types' => $this->mapearTipos($tiposRaw),
                    'contact_phone' => null,
                    'contact_email' => null,
                    'status' => 'approved',
                ],
            );
        }
    }
}
