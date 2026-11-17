<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 14px; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #2563eb; }
        .info-table { width: 100%; margin-bottom: 25px; }
        .info-table td { vertical-align: top; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th { background-color: #f1f5f9; text-align: left; padding: 10px; border-bottom: 1px solid #cbd5e1; }
        .details-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .totals { width: 40%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 10px; }
        .total-final { font-weight: bold; font-size: 16px; background-color: #eff6ff; color: #1e40af; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FACTURA COMERCIAL</div>
        <p><strong>N° de Control:</strong> {{ $invoice->invoice_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>Facturado a:</strong><br>
                {{ $invoice->customer->name }}<br>
                RIF / ID Fiscal: {{ $invoice->customer->tax_id }}<br>
                Correo: {{ $invoice->customer->email ?? 'N/A' }}
            </td>
            <td style="text-align: right;">
                <strong>Fecha de Emisión:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}<br>
                <strong>Fecha de Vencimiento:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}<br>
                <strong>Estado:</strong> {{ strtoupper($invoice->status) }}
            </td>
        </tr>
    </table>

    <table class="details-table">
        <thead>
            <tr>
                <th>Descripción / Concepto</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->notes ?: 'Servicios Profesionales / Facturación Corporativa' }}</td>
                <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Impuestos (IVA):</strong></td>
            <td style="text-align: right;">${{ number_format($invoice->tax, 2) }}</td>
        </tr>
        <tr class="total-final">
            <td>TOTAL A PAGAR:</td>
            <td style="text-align: right;">${{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>
</body>
</html>
