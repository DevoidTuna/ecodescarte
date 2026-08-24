<?php

namespace Tests\Feature;

use App\Models\CollectionPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobre a fronteira central da aplicação: um ponto sugerido pelo público só
 * chega ao mapa depois que a equipe o aprova, e as rotas de administração não
 * são alcançáveis sem token.
 */
class CollectionPointApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cria um membro da equipe e devolve o cabeçalho de autorização dele.
     *
     * @return array<string, string>
     */
    private function teamHeaders(): array
    {
        User::factory()->create([
            'username' => 'equipe',
            'password' => 'secret',
        ]);

        $token = $this->postJson('/api/login', [
            'username' => 'equipe',
            'password' => 'secret',
        ])->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * O payload mínimo que o formulário público envia.
     *
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Farmácia do Bairro',
            'address' => 'Rua das Flores, 100',
            'latitude' => -26.9077,
            'longitude' => -48.6619,
            'waste_types' => ['medicamentos'],
        ], $overrides);
    }

    public function test_the_public_map_lists_only_approved_points(): void
    {
        CollectionPoint::factory()->approved()->create(['name' => 'Ecoponto Central']);
        CollectionPoint::factory()->create(['name' => 'Sugestão Pendente']);

        $this->getJson('/api/points')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Ecoponto Central'])
            ->assertJsonMissing(['name' => 'Sugestão Pendente']);
    }

    public function test_a_submitted_point_is_pending_even_if_the_request_asks_for_approved(): void
    {
        // Uma sugestão do público não pode se autoaprovar forjando o campo status.
        $this->postJson('/api/points', $this->validPayload(['status' => 'approved']))
            ->assertCreated()
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('collection_points', [
            'name' => 'Farmácia do Bairro',
            'status' => 'pending',
        ]);

        $this->getJson('/api/points')->assertJsonCount(0);
    }

    public function test_submitting_a_point_rejects_an_unknown_waste_type(): void
    {
        $this->postJson('/api/points', $this->validPayload(['waste_types' => ['plutonio']]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('waste_types.0');
    }

    public function test_submitting_a_point_requires_coordinates_within_range(): void
    {
        $this->postJson('/api/points', $this->validPayload(['latitude' => 120]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('latitude');
    }

    public function test_admin_endpoints_are_unreachable_without_a_token(): void
    {
        $point = CollectionPoint::factory()->create();

        $this->getJson('/api/admin/points')->assertUnauthorized();
        $this->getJson('/api/admin/points/pending')->assertUnauthorized();
        $this->patchJson("/api/admin/points/{$point->id}/approve")->assertUnauthorized();
        $this->deleteJson("/api/admin/points/{$point->id}")->assertUnauthorized();
    }

    public function test_admin_endpoints_reject_an_invalid_token(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer nao-e-um-token-valido'])
            ->getJson('/api/admin/points')
            ->assertUnauthorized();
    }

    public function test_approving_a_point_publishes_it_to_the_public_map(): void
    {
        $headers = $this->teamHeaders();
        $point = CollectionPoint::factory()->create(['name' => 'Ecoponto Novo']);

        $this->getJson('/api/points')->assertJsonCount(0);

        $this->withHeaders($headers)
            ->patchJson("/api/admin/points/{$point->id}/approve")
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->getJson('/api/points')
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Ecoponto Novo']);
    }

    public function test_the_pending_queue_lists_only_points_awaiting_approval(): void
    {
        $headers = $this->teamHeaders();
        CollectionPoint::factory()->create(['name' => 'Aguardando']);
        CollectionPoint::factory()->approved()->create(['name' => 'Já Aprovado']);

        $this->withHeaders($headers)
            ->getJson('/api/admin/points/pending')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Aguardando']);

        // A listagem geral da equipe, ao contrário, mostra os dois.
        $this->withHeaders($headers)
            ->getJson('/api/admin/points')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_a_team_member_can_delete_a_point(): void
    {
        $headers = $this->teamHeaders();
        $point = CollectionPoint::factory()->approved()->create();

        $this->withHeaders($headers)
            ->deleteJson("/api/admin/points/{$point->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('collection_points', ['id' => $point->id]);
    }
}
