<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requiere_autenticacion_para_acceder_al_panel_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_it_bloquea_acciones_criticas_a_usuarios_no_autenticados(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);

        $response = $this->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.login'));
        $this->assertNotNull(Order::find($order->id));
    }

    public function test_it_permite_acceso_al_admin_con_rol_correcto(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }
}
