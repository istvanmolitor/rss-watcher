<?php

use Illuminate\Support\Facades\Route;
use Molitor\RssWatcher\Http\Controllers\RssFeedController;
use Molitor\RssWatcher\Http\Controllers\RssFeedItemController;

// RSS Watcher routes
Route::prefix('rss-watcher')
    ->middleware(['api', 'auth:sanctum'])
    ->name('rss-watcher.')
    ->group(function () {
        // RSS Feeds
        Route::resource('feeds', RssFeedController::class);

        // RSS Feed Items
        Route::resource('items', RssFeedItemController::class)->only(['index', 'show', 'destroy']);
    });

