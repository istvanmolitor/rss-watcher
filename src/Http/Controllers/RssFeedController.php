<?php
namespace Molitor\RssWatcher\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\RssWatcher\Http\Requests\RssFeedRequest;
use Molitor\RssWatcher\Http\Resources\RssFeedResource;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Services\RssWatcherService;
use OpenApi\Attributes as OA;
class RssFeedController extends Controller
{
    public function __construct(private RssWatcherService $rssWatcherService)
    {
    }
    #[OA\Get(
        path: "/api/rss-watcher/feeds",
        summary: "Get all RSS feeds",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = RssFeed::query();
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }
        $sortField = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $perPage = $request->input('per_page', 15);
        $feeds = $query->paginate($perPage);
        return response()->json([
            'data' => RssFeedResource::collection($feeds->items()),
            'meta' => [
                'current_page' => $feeds->currentPage(),
                'last_page' => $feeds->lastPage(),
                'per_page' => $feeds->perPage(),
                'total' => $feeds->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }
    #[OA\Get(
        path: "/api/rss-watcher/feeds/{id}",
        summary: "Get RSS feed by ID",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Feed not found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $feed = RssFeed::findOrFail($id);
        return response()->json(['data' => new RssFeedResource($feed)]);
    }
    #[OA\Post(
        path: "/api/rss-watcher/feeds",
        summary: "Create a new RSS feed",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 201, description: "Feed created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(RssFeedRequest $request): JsonResponse
    {
        $feed = RssFeed::create($request->validated());
        return response()->json(['data' => new RssFeedResource($feed)], 201);
    }
    #[OA\Put(
        path: "/api/rss-watcher/feeds/{id}",
        summary: "Update RSS feed",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Feed updated"),
            new OA\Response(response: 404, description: "Feed not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(RssFeedRequest $request, int $id): JsonResponse
    {
        $feed = RssFeed::findOrFail($id);
        $feed->update($request->validated());
        return response()->json(['data' => new RssFeedResource($feed)]);
    }
    #[OA\Post(
        path: "/api/rss-watcher/feeds/{id}/fetch",
        summary: "Fetch RSS feed items",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Feed fetched successfully"),
            new OA\Response(response: 404, description: "Feed not found"),
            new OA\Response(response: 500, description: "Fetch failed")
        ]
    )]
    public function fetch(int $id): JsonResponse
    {
        $feed = RssFeed::findOrFail($id);
        try {
            $this->rssWatcherService->fetchFeed($feed);
            return response()->json(['message' => __('rss-watcher::common.feed_fetched_successfully')]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/rss-watcher/feeds/{id}",
        summary: "Delete RSS feed",
        tags: ["RSS Watcher"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 204, description: "Feed deleted"),
            new OA\Response(response: 404, description: "Feed not found")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $feed = RssFeed::findOrFail($id);
        $feed->delete();
        return response()->json(null, 204);
    }
}
