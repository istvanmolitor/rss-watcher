<?php

namespace Molitor\RssWatcher\Repositories;

use Illuminate\Support\Collection;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;

class RssFeedItemRepository implements RssFeedItemRepositoryInterface
{
    protected RssFeedItem $rssFeedItem;

    public function __construct()
    {
        $this->rssFeedItem = new RssFeedItem();
    }

    public function createRssFeedItem(RssFeed $feed, string $guid, string $title, string $link, string $description, string|null $image, string $publishedAt): RssFeedItem
    {
        return $this->rssFeedItem->create([
            'rss_feed_id' => $feed->id,
            'guid' => $guid,
            'title' => $title,
            'link' => $link,
            'description' => $description,
            'image' => $image,
            'published_at' => $publishedAt,
        ]);
    }

    public function updateRssFeedItem(RssFeedItem $item, string $title, string $link, string $description, string|null $image, string $publishedAt): RssFeedItem
    {
        $item->fill([
            'title' => $title,
            'link' => $link,
            'description' => $description,
            'image' => $image,
            'published_at' => $publishedAt,
        ]);
        $item->save();
        return $item;
    }

    public function getByFeed(RssFeed $feed): Collection
    {
        return $this->rssFeedItem->where('rss_feed_id', $feed->id)->get();
    }

    public function deleteRssFeedItem(RssFeedItem $item): bool
    {
        $item->delete();
        return true;
    }
}
