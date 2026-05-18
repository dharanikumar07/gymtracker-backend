<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'category_name' => \App\Http\Helpers\Helper::deslugifyCategory($this->category_type),
            'default_amount' => (float) $this->default_amount,
            'expense_period' => $this->expense_period,
            'plan_uuid' => $this->plan_uuid,
        ];
    }
}
