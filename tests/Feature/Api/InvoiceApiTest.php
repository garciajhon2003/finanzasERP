<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_invoices(): void
    {
        $response = $this->getJson('/api/invoices');

        $response->assertStatus(401);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@intranet.test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@intranet.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_authenticated_user_can_list_invoices(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Cliente Prueba C.A.',
            'tax_id' => 'J-12345678-9',
        ]);

        Invoice::createQuietly([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'invoice_number' => 'FAC-TEST-001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 1000,
            'tax' => 160,
            'total' => 1160,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'invoice_number' => 'FAC-TEST-001',
                'total' => 1160,
            ]);
    }

    public function test_authenticated_user_can_create_invoice_via_api(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Empresa Nueva S.A.',
            'tax_id' => 'J-98765432-1',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'customer_id' => $customer->id,
            'invoice_number' => 'FAC-API-999',
            'issue_date' => '2026-11-14',
            'due_date' => '2026-11-30',
            'subtotal' => 500,
        ];

        $response = $this->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.invoice_number', 'FAC-API-999')
            ->assertJsonPath('data.total', 580); // 500 + 16% IVA calculado automático

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'FAC-API-999',
            'total' => 580,
        ]);
    }
}
