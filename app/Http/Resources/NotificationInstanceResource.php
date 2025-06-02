<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationInstanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->notification->type,
            'content' => $this->content,
            'metadata' => $this->metadata,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}
