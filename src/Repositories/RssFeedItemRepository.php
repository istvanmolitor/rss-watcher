<?php

namespace Molitor\RssWatcher\Repositories;

use Molitor\RssWatcher\Events\RssFeedItemCreated;
use Molitor\RssWatcher\Events\RssFeedItemChanged;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;

class RssFeedItemRepository implements RssFeedItemRepositoryInterface
{
    protected RssFeedItem $rssFeedItem;

    public function __construct()
    {
        $this->rssFeedItem = new RssFeedItem();
    }

    public function getByGuid(RssFeed $rssFeed, string $guid): null|RssFeedItem
    {
        return $this->rssFeedItem->where('rss_feed_id', $rssFeed->id)->where('guid', $guid)->first();
    }

    public function getExistingLinksByFeed(RssFeed $feed): array
    {
        return RssFeedItem::where('rss_feed_id', $feed->id)->pluck('link')->all();
    }

    public function getExistingIdsByGuidForFeed(RssFeed $feed): array
    {
        return RssFeedItem::where('rss_feed_id', $feed->id)
            ->whereNotNull('guid')
            ->pluck('id', 'guid')
            ->all();
    }

    public function create(array $data): RssFeedItem
    {
        $item = RssFeedItem::create($data);

        RssFeedItemChanged::dispatch('created', [$item->id]);
        return $item;
    }

    public function updateById(int $id, array $data): void
    {
        RssFeedItem::where('id', $id)->update($data);
        RssFeedItemChanged::dispatch('updated', [$id]);
    }

    public function updateByFeedAndLink(RssFeed $feed, string $link, array $data): void
    {
        RssFeedItem::where('rss_feed_id', $feed->id)
            ->where('link', $link)
            ->update($data);
        RssFeedItemChanged::dispatch('updated', null, ['rss_feed_id' => $feed->id, 'link' => $link]);
    }
}
