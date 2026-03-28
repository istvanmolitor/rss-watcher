<?php

namespace Molitor\RssWatcher\Repositories;

use Illuminate\Support\Collection;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;

interface RssFeedItemRepositoryInterface
{
    public function getByFeed(RssFeed $feed): Collection;

    public function createRssFeedItem(RssFeed $feed, string $guid, string $title, string $link, string $description, ?string $image, string $publishedAt): RssFeedItem;

    public function updateRssFeedItem(RssFeedItem $item, string $title, string $link, string $description, ?string $image, string $publishedAt): RssFeedItem;

    public function deleteRssFeedItem(RssFeedItem $item): bool;
}
