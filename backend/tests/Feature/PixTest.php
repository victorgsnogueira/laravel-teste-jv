<?php

namespace Tests\Feature;

use App\Models\Pix;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PixTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    public function test_authenticated_user_can_create_pix(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/pix', [
                'amount' => 100.50
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'amount',
                    'expires_at',
                    'qr_code_url'
                ]
            ]);

        $this->assertDatabaseHas('pixes', [
            'user_id' => $this->user->id,
            'amount' => 100.50,
            'status' => 'generated'
        ]);
    }

    public function test_unauthenticated_user_cannot_create_pix(): void
    {
        $response = $this->postJson('/api/pix', [
            'amount' => 100.50
        ]);

        $response->assertUnauthorized();
    }

    public function test_pix_can_be_marked_as_paid(): void
    {
        $pix = Pix::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'generated',
            'expires_at' => now()->addMinutes(10)
        ]);

        $response = $this->getJson("/api/pix/{$pix->token}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Pagamento confirmado com sucesso',
                'status' => 'paid'
            ]);

        $this->assertDatabaseHas('pixes', [
            'id' => $pix->id,
            'status' => 'paid'
        ]);
    }

    public function test_expired_pix_is_marked_as_expired(): void
    {
        $pix = Pix::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'generated',
            'expires_at' => now()->subMinutes(1)
        ]);

        $response = $this->getJson("/api/pix/{$pix->token}");

        $response->assertOk()
            ->assertJson([
                'message' => 'PIX expirado',
                'status' => 'expired'
            ]);

        $this->assertDatabaseHas('pixes', [
            'id' => $pix->id,
            'status' => 'expired'
        ]);
    }

    public function test_can_get_pix_statistics(): void
    {
        // Criar alguns PIX com diferentes status
        Pix::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'generated'
        ]);

        Pix::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'paid'
        ]);

        Pix::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'expired'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/pix/stats');

        $response->assertOk()
            ->assertJson([
                'generated' => 2,
                'paid' => 3,
                'expired' => 1
            ]);
    }
}
