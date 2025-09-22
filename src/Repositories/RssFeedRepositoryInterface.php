<?php

namespace Molitor\RssWatcher\Repositories;

use Illuminate\Support\Collection;
use Molitor\RssWatcher\Models\RssFeed;

interface RssFeedRepositoryInterface
{
    /** @return Collection<int,RssFeed> */
    public function all(): Collection;

    public function touchFetchedAt(RssFeed $feed): void;
}
