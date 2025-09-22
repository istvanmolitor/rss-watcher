<?php

namespace Molitor\RssWatcher\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Molitor\RssWatcher\Models\RssFeedItem;

class RssFeedItemChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RssFeedItem $rssFeedItem
    ) {
    }
}
