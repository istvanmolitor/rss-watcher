<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Services\RssWatcherService;
use Tests\TestCase;
use willvincent\Feeds\Facades\FeedsFacade;

class RssWatcherServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_feed_handles_invalid_url_and_incorrect_exception(): void
    {
        $feed = RssFeed::create([
            'name' => 'Test Feed',
            'url' => 'http://invalid-url.com',
            'enabled' => true,
        ]);

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with($feed->url)
            ->andReturn(null);

        $service = app(RssWatcherService::class);

        // It should throw an \Exception, not PHPUnit\Framework\Exception (which it used to)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('rss-watcher::common.exception_invalid_feed', ['url' => $feed->url]));

        $service->fetchFeed($feed);
    }
}

