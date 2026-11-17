<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // Listar todas las facturas paginadas con filtro opcional por estado
    public function index(Request $request)
    {
        $query = Invoice::with('customer')->latest('issue_date');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return InvoiceResource::collection($query->paginate(10));
    }

    // Mostrar una factura específica
    public function show(Invoice $invoice)
    {
        $invoice->load('customer');

        return new InvoiceResource($invoice);
    }

    // Crear una nueva factura desde la API
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'subtotal' => 'required|numeric|min:0.01',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $tax = $validated['tax'] ?? round($validated['subtotal'] * 0.16, 2);

        $invoice = Invoice::create([
            'customer_id' => $validated['customer_id'],
            'user_id' => $request->user()->id,
            'invoice_number' => $validated['invoice_number'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $validated['subtotal'],
            'tax' => $tax,
            'total' => $validated['subtotal'] + $tax,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return (new InvoiceResource($invoice->load('customer')))
            ->response()
            ->setStatusCode(201);
    }
}
