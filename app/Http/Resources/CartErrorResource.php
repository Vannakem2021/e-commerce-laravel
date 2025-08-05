<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartErrorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => false,
            'message' => $this->resource['message'] ?? 'An error occurred',
            'errors' => $this->resource['errors'] ?? [],
            'code' => $this->resource['code'] ?? 'CART_ERROR',
            'timestamp' => now()->toISOString(),
        ];
    }
}
