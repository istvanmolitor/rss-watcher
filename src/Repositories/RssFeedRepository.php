<?php

namespace Molitor\RssWatcher\Repositories;

use Illuminate\Support\Collection;
use Molitor\RssWatcher\Models\RssFeed;

class RssFeedRepository implements RssFeedRepositoryInterface
{
    public function all(): Collection
    {
        return RssFeed::all();
    }

    public function touchFetchedAt(RssFeed $feed): void
    {
        $feed->last_fetched_at = now();
        $feed->save();
    }
}
