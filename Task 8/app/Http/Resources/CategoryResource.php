<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            // whenLoaded bikin relasi cuma ikut kalau memang di-eager load.
            // Tanpa ini gampang kejebak N+1 query waktu nampilin daftar.
            'items_count' => $this->whenCounted('items'),
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
