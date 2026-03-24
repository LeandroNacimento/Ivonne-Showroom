<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requiere autenticacion para acceder al panel admin', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));
});

it('bloquea acciones criticas a usuarios no autenticados', function () {
    $client = Client::factory()->create();
    $order = Order::factory()->create(['client_id' => $client->id]);

    $response = $this->delete(route('admin.orders.destroy', $order));
    // Should redirect to login since user is not authenticated
    $response->assertRedirect(route('admin.login'));

    // Order should still exist
    expect(Order::find($order->id))->not->toBeNull();
});

it('permite acceso al admin con rol correcto', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();
});
