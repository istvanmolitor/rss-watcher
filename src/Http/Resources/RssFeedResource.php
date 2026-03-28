<?php

namespace Molitor\RssWatcher\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RssFeed",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Tech News"),
        new OA\Property(property: "url", type: "string", example: "https://example.com/feed.xml"),
        new OA\Property(property: "enabled", type: "boolean", example: true),
        new OA\Property(property: "last_fetched_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
        new OA\Property(property: "items_count", type: "integer", example: 10),
    ]
)]
class RssFeedResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->url,
            'enabled' => $this->enabled,
            'last_fetched_at' => $this->last_fetched_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'items_count' => $this->whenCounted('items'),
        ];
    }
}

