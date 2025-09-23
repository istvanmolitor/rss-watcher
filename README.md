# Laravel RSS Watcher

A lightweight Laravel package that watches one or more RSS feeds and dispatches Laravel events when items are created or changed.

It includes:
- Database tables for feeds and feed items (migrations auto‑loaded by the service provider)
- An Artisan command to fetch/update feeds
- A scheduler hook to run the fetch every 10 minutes by default
- Events you can listen to in order to react to new or updated items

## Requirements
- PHP 8.1+
- Laravel 10+

## Installation

Install the package via Composer:

```
composer require molitor/rss-watcher
```

Laravel package auto‑discovery will register the service provider. If you have disabled auto‑discovery, add the provider manually to `config/app.php`:

```
'providers' => [
    // ...
    Molitor\RssWatcher\Providers\RssWatcherServiceProvider::class,
],
```

## Database

The package automatically loads its migrations. Run:

```
php artisan migrate
```

This will create two tables:
- rss_feeds (id, name, url, last_fetched_at, timestamps)
- rss_feed_items (id, rss_feed_id, guid, hash, title, link, description, published_at, timestamps)

## Usage

1) Create feeds you want to watch (e.g., via seeder, tinker, or a CRUD UI) by inserting rows into `rss_feeds` with at least `name` and `url`.

Example using tinker:

```
php artisan tinker
>>> Molitor\RssWatcher\Models\RssFeed::create([
...     'name' => 'Laravel News',
...     'url' => 'https://laravel-news.com/feed',
... ]);
```

2) Fetch feeds on demand by running the Artisan command:

```
php artisan rss-watcher:fetch
```

The command loops through all records in `rss_feeds`, fetches the RSS content, stores new items, updates changed items, and dispatches events.

## Scheduling

The service provider registers a scheduler hook to run the fetch command every 10 minutes automatically:

- Command: `rss-watcher:fetch`
- Frequency: every ten minutes

If you already use Laravel's scheduler, ensure your system cron triggers `php artisan schedule:run` every minute as recommended by Laravel.

## Events

You can listen to these events to react to changes in your feeds:

- Molitor\RssWatcher\Events\RssFeedItemCreated
  - Dispatched when a new item is stored for a feed
  - Public property: `item` (Molitor\RssWatcher\Models\RssFeedItem)

- Molitor\RssWatcher\Events\RssFeedItemChanged
  - Dispatched when an existing item's content changes
  - Public property: `rssFeedItem` (Molitor\RssWatcher\Models\RssFeedItem)

Example listener registration in `app/Providers/EventServiceProvider.php`:

```
protected $listen = [
    Molitor\RssWatcher\Events\RssFeedItemCreated::class => [
        App\Listeners\OnRssItemCreated::class,
    ],
    Molitor\RssWatcher\Events\RssFeedItemChanged::class => [
        App\Listeners\OnRssItemChanged::class,
    ],
];
```

Example listener class (simplified):

```
namespace App\Listeners;

use Molitor\RssWatcher\Events\RssFeedItemChanged;
use Molitor\RssWatcher\Events\RssFeedItemCreated;

class OnRssItemCreated
{
    public function handle(RssFeedItemCreated $event): void
    {
        // $event->item is Molitor\RssWatcher\Models\RssFeedItem
        // Do something, e.g., dispatch a job or notify users
    }
}

class OnRssItemChanged
{
    public function handle(RssFeedItemChanged $event): void
    {
        // $event->rssFeedItem is Molitor\RssWatcher\Models\RssFeedItem
        // React to updated content
    }
}
```

## How change detection works

- Items are keyed by their GUID when available. A content hash is computed from title, link, description, and published_at.
- If an existing item's computed hash changes, the item is updated and `RssFeedItemChanged` is dispatched.
- When a new GUID appears, it is stored and `RssFeedItemCreated` is dispatched.

Note: The `link` field is unique in the database. Ensure your feeds provide unique links per item.

## Troubleshooting

- Make sure the `willvincent/feeds` dependency can fetch your target URLs (network access, SSL certs, etc.).
- If the scheduler isn't running, verify your system cron is set up and that `php artisan schedule:run` executes.
- After adding new feeds, run `php artisan rss-watcher:fetch` manually to test.

## Testing locally

- Seed one or more feeds
- Run the fetch command and watch your logs/listeners
- Optionally, write application tests that fake events and assert dispatching

## License

MIT
