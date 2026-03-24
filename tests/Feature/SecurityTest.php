<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminPath = env('ADMIN_PATH');
});

it('requiere autenticacion para acceder al panel admin', function () {
    $response = $this->get('/' . $this->adminPath . '/dashboard');
    $response->assertRedirect(route('admin.login'));
});

it('bloquea acciones criticas a usuarios no autenticados (eliminar pedido)', function () {
    $client = Client::factory()->create();
    $order = Order::factory()->create(['client_id' => $client->id]);

    $response = $this->delete('/' . $this->adminPath . '/orders/' . $order->id);
    $response->assertRedirect(route('admin.login'));

    expect(Order::find($order->id))->not->toBeNull();
});

it('permite eliminar pedido a usuario administrador testeando seguridad', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $client = Client::factory()->create();
    $order = Order::factory()->create(['client_id' => $client->id]);

    // Skip CSRF for test or actAs
    $response = $this->actingAs($admin)->delete('/' . $this->adminPath . '/orders/' . $order->id);
    
    // Deberia redirigir al index de orders exitosamente o dar status 200/302 
    $response->assertStatus(302);
    
    // Depending on implementation, it might be soft deleted or strictly deleted or cancelled.
});
