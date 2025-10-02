<?php

namespace Molitor\RssWatcher\Repositories;

use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;
use Illuminate\Support\Collection;

interface RssFeedItemRepositoryInterface
{
    public function getByFeed(RssFeed $feed): Collection;

    public function createRssFeedItem(RssFeed $feed, string $guid, string $title, string $link, string $description, string|null $image, string $publishedAt): RssFeedItem;

    public function updateRssFeedItem(RssFeedItem $item, string $title, string $link, string $description, string|null $image, string $publishedAt): RssFeedItem;

    public function deleteRssFeedItem(RssFeedItem $item): bool;
}
