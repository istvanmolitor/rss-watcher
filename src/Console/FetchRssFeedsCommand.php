<?php

namespace Molitor\RssWatcher\Console;

use Illuminate\Console\Command;
use Molitor\RssWatcher\Repositories\RssFeedItemRepositoryInterface;
use Molitor\RssWatcher\Repositories\RssFeedRepositoryInterface;
use Molitor\RssWatcher\Services\RssWatcherService;

class FetchRssFeedsCommand extends Command
{
    protected $signature = 'rss-watcher:fetch';

    public function __construct(
        protected RssFeedRepositoryInterface     $rssFeedRepository,
        protected RssFeedItemRepositoryInterface $rssFeedItemRepository
    ) {
        $this->description = __('rss-watcher::common.console_fetch_description');
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var RssWatcherService $service */
        $service = app(RssWatcherService::class);

        $feeds = $this->rssFeedRepository->getEnabledFeeds();
        foreach ($feeds as $feed) {
            $this->info(__('rss-watcher::common.console_fetching', ['name' => $feed->name]));
            $service->fetchFeed($feed);
        }

        return self::SUCCESS;
    }
}
