<?php

namespace Molitor\RssWatcher\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RssFeedItem",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "rss_feed_id", type: "integer", example: 1),
        new OA\Property(property: "guid", type: "string", example: "https://example.com/article-1"),
        new OA\Property(property: "title", type: "string", example: "Breaking News"),
        new OA\Property(property: "link", type: "string", example: "https://example.com/article-1"),
        new OA\Property(property: "description", type: "string", nullable: true),
        new OA\Property(property: "image", type: "string", nullable: true),
        new OA\Property(property: "published_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
        new OA\Property(
            property: "feed",
            ref: "#/components/schemas/RssFeed",
            nullable: true
        ),
    ]
)]
class RssFeedItemResource extends JsonResource
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
            'rss_feed_id' => $this->rss_feed_id,
            'guid' => $this->guid,
            'title' => $this->title,
            'link' => $this->link,
            'description' => $this->description,
            'image' => $this->image,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'feed' => new RssFeedResource($this->whenLoaded('feed')),
        ];
    }
}

