<?php

namespace Molitor\RssWatcher\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Molitor\RssWatcher\Console\FetchRssFeedsCommand;
use Molitor\RssWatcher\Repositories\RssFeedRepository;
use Molitor\RssWatcher\Repositories\RssFeedItemRepository;
use Molitor\RssWatcher\Repositories\RssFeedItemRepositoryInterface;
use Molitor\RssWatcher\Repositories\RssFeedRepositoryInterface;

class RssWatcherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RssFeedRepositoryInterface::class, RssFeedRepository::class);
        $this->app->bind(RssFeedItemRepositoryInterface::class, RssFeedItemRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'rss-watcher');

        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchRssFeedsCommand::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('rss-watcher:fetch')->everyTenMinutes();
        });
    }
}
