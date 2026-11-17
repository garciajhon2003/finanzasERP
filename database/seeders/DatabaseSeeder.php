<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
            ]
        );

        if (\Spatie\Permission\Models\Role::where('name', 'super_admin')->exists()) {
            $admin->assignRole('super_admin');
        }

        // 1. Categorías de gastos con colores fijos para el gráfico circular
        $categories = [
            ['name' => 'Nómina y Sueldos', 'type' => 'expense', 'color' => '#3b82f6'],
            ['name' => 'Servicios e Internet', 'type' => 'expense', 'color' => '#10b981'],
            ['name' => 'Equipamiento y Software', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Impuestos y Tasas', 'type' => 'expense', 'color' => '#f59e0b'],
            ['name' => 'Marketing y Ventas', 'type' => 'expense', 'color' => '#ef4444'],
        ];

        $categoryModels = collect($categories)->map(
            fn($cat) => Category::firstOrCreate(['name' => $cat['name']], $cat)
        );

        // 2. Clientes B2B
        $customers = collect([
            ['name' => 'Corporación Yamashita C.A.', 'tax_id' => 'J-30123456-1', 'email' => 'pagos@yamashita.com'],
            ['name' => 'Inversiones TechGlobal', 'tax_id' => 'J-40987654-2', 'email' => 'finanzas@techglobal.com'],
            ['name' => 'Logística del Centro S.A.', 'tax_id' => 'J-29876543-9', 'email' => 'cuentas@logisticacentro.com'],
            ['name' => 'Grupo Comercial Andes', 'tax_id' => 'J-31456789-0', 'email' => 'admin@grupoandes.com'],
        ])->map(fn($cust) => Customer::firstOrCreate(['tax_id' => $cust['tax_id']], $cust));

        // 3. Proveedores
        $suppliers = collect([
            ['name' => 'FibraNet Telecom', 'tax_id' => 'J-50111222-3', 'contact_email' => 'soporte@fibranet.com'],
            ['name' => 'Suministros Oficina Express', 'tax_id' => 'J-50333444-5', 'contact_email' => 'ventas@oficinaexpress.com'],
            ['name' => 'CloudHosting Solutions', 'tax_id' => 'J-50555666-7', 'contact_email' => 'billing@cloudhosting.com'],
        ])->map(fn($sup) => Supplier::firstOrCreate(['tax_id' => $sup['tax_id']], $sup));

        // 4. Generar 12 meses de facturas y gastos para llenar los gráficos
        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create(now()->year, $month, 15);

            // Crear 3 facturas por mes
            for ($i = 1; $i <= 3; $i++) {
                $subtotal = rand(1200, 4500);
                $tax = round($subtotal * 0.16, 2);
                $status = ($month < now()->month) ? 'paid' : ($i === 1 ? 'paid' : ($i === 2 ? 'pending' : 'overdue'));

                Invoice::createQuietly([
                    'customer_id' => $customers->random()->id,
                    'user_id' => $admin->id,
                    'invoice_number' => 'FAC-' . now()->year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . $i,
                    'issue_date' => $date->copy()->subDays(rand(1, 10)),
                    'due_date' => $date->copy()->addDays(15),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'status' => $status,
                ]);
            }

            // Crear 4 gastos por mes
            foreach ($categoryModels as $index => $category) {
                $amount = match ($index) {
                    0 => rand(1800, 2400), // Nómina (mayor porcentaje)
                    1 => rand(250, 450),   // Servicios
                    2 => rand(400, 900),   // Equipos
                    3 => rand(300, 600),   // Impuestos
                    default => rand(150, 400),
                };

                $expenseStatus = ($month === now()->month && $index === 4) ? 'pending' : 'approved';

                Expense::createQuietly([
                    'supplier_id' => $index === 0 ? null : $suppliers->random()->id,
                    'category_id' => $category->id,
                    'user_id' => $admin->id,
                    'concept' => "Pago mensual - {$category->name} (Mes {$month})",
                    'amount' => $amount,
                    'expense_date' => $date->copy()->subDays(rand(1, 12)),
                    'status' => $expenseStatus,
                ]);
            }
        }
    }
}
