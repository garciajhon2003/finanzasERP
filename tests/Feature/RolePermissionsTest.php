<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('accountant role can create invoices but cannot delete them or manage users', function () {
    // Creamos los permisos básicos que genera Filament Shield
    $viewInvoice = Permission::create(['name' => 'view_any_invoice']);
    $createInvoice = Permission::create(['name' => 'create_invoice']);
    $deleteInvoice = Permission::create(['name' => 'delete_invoice']);
    $viewUser = Permission::create(['name' => 'view_any_user']);

    $contadorRole = Role::create(['name' => 'Contador']);
    $contadorRole->givePermissionTo([$viewInvoice, $createInvoice]);

    $contador = User::factory()->create();
    $contador->assignRole($contadorRole);

    expect($contador->can('create_invoice'))->toBeTrue()
        ->and($contador->can('delete_invoice'))->toBeFalse()
        ->and($contador->can('view_any_user'))->toBeFalse();
});
