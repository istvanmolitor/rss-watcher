<?php

namespace Molitor\RssWatcher\Console;

use Illuminate\Console\Command;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;
use Molitor\RssWatcher\Providers\RssWatcherServiceProvider;
use Molitor\RssWatcher\Repositories\RssFeedItemRepositoryInterface;
use Molitor\RssWatcher\Repositories\RssFeedRepositoryInterface;
use Molitor\RssWatcher\Services\RssWatcherService;
use willvincent\Feeds\Facades\FeedsFacade;

class FetchRssFeedsCommand extends Command
{
    protected $signature = 'rss-:fetch';
    protected $description = 'Fetch and refresh RSS feeds';

    public function __construct(
        protected RssFeedRepositoryInterface     $rssFeedRepository,
        protected RssFeedItemRepositoryInterface $rssFeedItemRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var RssWatcherService $service */
        $service = app(RssWatcherService::class);

        $feeds = $this->rssFeedRepository->all();
        foreach ($feeds as $feed) {
            $this->info("Fetching {$feed->name}...");
            $service->fetchFeed($feed);
        }

        return self::SUCCESS;
    }
}
