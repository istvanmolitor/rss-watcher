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
        Route::post('feeds/{id}/fetch', [RssFeedController::class, 'fetch'])->name('feeds.fetch');
        Route::resource('feeds', RssFeedController::class);

        // RSS Feed Items
        Route::resource('items', RssFeedItemController::class)->only(['index', 'show', 'destroy']);
    });

