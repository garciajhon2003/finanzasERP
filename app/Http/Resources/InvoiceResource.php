<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'tax_id' => $this->customer?->tax_id,
            ],
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
