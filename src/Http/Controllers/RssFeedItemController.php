<?php

namespace Molitor\RssWatcher\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Molitor\RssWatcher\DataTables\RssFeedItemDataTable;
use Molitor\RssWatcher\Http\Resources\RssFeedItemResource;
use Molitor\RssWatcher\Models\RssFeedItem;
use OpenApi\Attributes as OA;

class RssFeedItemController extends Controller
{
    #[OA\Get(
        path: '/api/rss-watcher/items',
        summary: 'Get all RSS feed items',
        tags: ['RSS Watcher'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'feed_id',
                in: 'query',
                description: 'Filter by feed ID',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Search term',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Sort field',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'published_at')
            ),
            new OA\Parameter(
                name: 'direction',
                in: 'query',
                description: 'Sort direction',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Items per page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/RssFeedItem')
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(RssFeedItemDataTable $dataTable): AnonymousResourceCollection
    {
        return $dataTable->getResponse();
    }

    #[OA\Get(
        path: '/api/rss-watcher/items/{id}',
        summary: 'Get RSS feed item by ID',
        tags: ['RSS Watcher'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Item ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/RssFeedItem'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Item not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $item = RssFeedItem::with('feed')->findOrFail($id);

        return response()->json([
            'data' => new RssFeedItemResource($item),
        ]);
    }

    #[OA\Delete(
        path: '/api/rss-watcher/items/{id}',
        summary: 'Delete RSS feed item',
        tags: ['RSS Watcher'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Item ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Item deleted'
            ),
            new OA\Response(response: 404, description: 'Item not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $item = RssFeedItem::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}
