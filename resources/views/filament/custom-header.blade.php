<div class="mb-6 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 p-6 text-white shadow-lg">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span
                class="inline-block rounded-full bg-blue-500/30 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-blue-100">
                Panel de Control Financiero
            </span>
            <h2 class="mt-2 text-2xl font-bold tracking-tight">
                Bienvenido de nuevo, {{ auth()->user()->name }}
            </h2>
            <p class="mt-1 text-sm text-blue-100">
                Monitorea el flujo de caja, facturas por cobrar y auditoría operativa en tiempo real.
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs uppercase text-blue-200">Fecha del Sistema</p>
            <p class="text-lg font-semibold">{{ now()->translatedFormat('d M, Y') }}</p>
        </div>
    </div>
</div>
