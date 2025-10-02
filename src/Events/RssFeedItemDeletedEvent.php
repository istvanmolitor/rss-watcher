<?php

namespace Molitor\RssWatcher\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Molitor\RssWatcher\Models\RssFeedItem;

class RssFeedItemDeletedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public RssFeedItem $item)
    {
    }
}
