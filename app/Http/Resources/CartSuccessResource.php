<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartSuccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->resource['message'] ?? 'Operation completed successfully',
            'data' => $this->resource['data'] ?? null,
            'cart_summary' => $this->resource['cart_summary'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
