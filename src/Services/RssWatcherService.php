<?php

namespace Molitor\RssWatcher\Services;

use Molitor\RssWatcher\Events\RssFeedItemCreatedEvent;
use Molitor\RssWatcher\Events\RssFeedItemChangedEvent;
use Molitor\RssWatcher\Events\RssFeedItemDeletedEvent;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;
use Molitor\RssWatcher\Repositories\RssFeedItemRepositoryInterface;
use Molitor\RssWatcher\Repositories\RssFeedRepositoryInterface;
use PHPUnit\Framework\Exception;
use willvincent\Feeds\Facades\FeedsFacade;

class RssWatcherService
{
    protected null|RssFeed $feed = null;
    private array $oldItems = [];

    public function __construct(
        private RssFeedRepositoryInterface $rssFeedRepository,
        private RssFeedItemRepositoryInterface $rssFeedItemRepository
    )
    {
    }

    protected function loadOldItems(): void
    {
        $this->oldItems = [];
        /** @var RssFeedItem $item */
        foreach ($this->rssFeedItemRepository->getByFeed($this->feed) as $item) {
            $this->oldItems[$item->guid] = $item;
        }
    }

    protected function getGuidFromFeedItem($item): string
    {
        return trim($item->get_permalink());
    }

    protected function getDescriptionFromFeedItem($item): string
    {
        return strip_tags(trim($item->get_description()));
    }

    protected function getImageFromFeedItem($item): string|null
    {
        $enclosures = $item->get_enclosures();
        if(count($enclosures) > 0) {
            return $enclosures[0]->link;
        }
        return null;
    }

    public function fetchFeed(RssFeed $feed): void
    {
        $feedItems = FeedsFacade::make($feed->url);
        if (!$feedItems) {
            throw new Exception("Invalid feed: " . $feed->url);
        }

        if(!$this->isFeedInitialized($feed)) {
            $this->initFeed($feed);
        }

        $newGuids = [];
        foreach ($feedItems->get_items() as $feedItem) {
            if($feedItem) {
                $guid = $this->getGuidFromFeedItem($feedItem);
                if($guid) {
                    $item = $this->saveItem(
                        $guid,
                        $feedItem->get_title(),
                        $feedItem->get_permalink(),
                        $this->getDescriptionFromFeedItem($feedItem),
                        $this->getImageFromFeedItem($feedItem),
                        $feedItem->get_date('Y-m-d H:i:s')
                    );

                    if ($item) {
                        $newGuids[$guid] = $item;
                    }
                }
            }
        }

        $diff = array_diff(array_keys($this->oldItems), array_keys($newGuids));
        foreach ($diff as $guid) {
            $this->deleteItem($guid);
        }

        $this->rssFeedRepository->touchFetchedAt($this->feed);
    }

    public function isFeedInitialized(RssFeed $feed): bool
    {
        return $this->feed !== null && $feed->id === $this->feed->id;
    }

    public function initFeed(RssFeed $feed): void
    {
        $this->feed = $feed;
        $this->loadOldItems();
    }

    public function isItemExists(string $guid): bool
    {
        return $this->feed !== null && array_key_exists($guid, $this->oldItems);
    }

    protected function getHash(string $title, string $link, string $description, string|null $image, string $publishedAt): string
    {
        return md5(serialize([$title, $link, $description, $image, $publishedAt]));
    }

    protected function getHashByItem(RssFeedItem $rssFeedItem): string
    {
        return $this->getHash($rssFeedItem->title, $rssFeedItem->link, $rssFeedItem->description, $rssFeedItem->image, $rssFeedItem->published_at);
    }

    protected function saveItem(string $guid, string $title, string $link, string $description, string|null $image ,string $publishedAt): RssFeedItem
    {
        if($this->isItemExists($guid)) {
            /** @var RssFeedItem $item */
            $item = $this->oldItems[$guid];
            $hash = $this->getHash($title, $link, $description, $image, $publishedAt);
            $oldHash = $this->getHashByItem($item);

            if($oldHash !== $hash) {
                $this->rssFeedItemRepository->updateRssFeedItem($item, $title, $link, $description, $image, $publishedAt);
                event(new RssFeedItemChangedEvent($item));
            }
        }
        else {
            $item = $this->rssFeedItemRepository->createRssFeedItem($this->feed, $guid, $title, $link, $description, $image, $publishedAt);
            event(new RssFeedItemCreatedEvent($item));
        }
        return $item;
    }

    protected function deleteItem(string $guid): bool
    {
        if(!array_key_exists($guid, $this->oldItems)) {
            return false;
        }

        $item = $this->oldItems[$guid];
        $this->rssFeedItemRepository->deleteRssFeedItem($item);
        event(new RssFeedItemDeletedEvent($item));
        return true;
    }
}
