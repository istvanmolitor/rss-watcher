<?php

namespace Molitor\RssWatcher\Repositories;

use Illuminate\Support\Collection;
use Molitor\RssWatcher\Models\RssFeed;

class RssFeedRepository implements RssFeedRepositoryInterface
{
    protected RssFeed $rssFeed;

    public function __construct()
    {
        $this->rssFeed = new RssFeed();
    }

    public function all(): Collection
    {
        return $this->rssFeed->get();
    }

    public function touchFetchedAt(RssFeed $feed): void
    {
        $feed->last_fetched_at = now();
        $feed->save();
    }

    public function getEnabledFeeds(): Collection
    {
        return $this->rssFeed->where('enabled', true)->get();
    }
}
