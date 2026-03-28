<?php

namespace Molitor\RssWatcher\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\RssWatcher\Repositories\RssFeedRepositoryInterface;
use Molitor\RssWatcher\Services\RssWatcherService;

class FetchFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $feedId
    ) {}

    public function handle(RssWatcherService $service, RssFeedRepositoryInterface $repository): void
    {
        $feed = $repository->getById($this->feedId);
        if ($feed) {
            $service->fetchFeed($feed);
        }
    }
}
