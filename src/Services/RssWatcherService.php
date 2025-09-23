<?php

namespace Molitor\RssWatcher\Services;

use Molitor\RssWatcher\Events\RssFeedItemCreated;
use Molitor\RssWatcher\Events\RssFeedItemChanged;
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
        foreach ($this->feed->items() as $item) {
            $this->oldItems[$item->guid] = $item;
        }
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
            if($feedItem) {;
                $guid = $feedItem->get_guid();
                $item = $this->saveItem(
                    $guid,
                    $feedItem->get_title(),
                    $feedItem->get_permalink(),
                    $feedItem->get_description(),
                    $feedItem->get_date('Y-m-d H:i:s')
                );

                if ($item) {
                    $newGuids[$guid] = $item;
                }
            }
        }

        foreach (array_diff(array_values($this->oldItems), $newGuids) as $guid) {
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

    protected function getHash(string $title, string $link, string $description, string $publishedAt): string
    {
        return md5(serialize([$title, $link, $description, $publishedAt]));
    }

    protected function getHashByItem(RssFeedItem $rssFeedItem): string
    {
        return $this->getHash($rssFeedItem->title, $rssFeedItem->link, $rssFeedItem->description, $rssFeedItem->published_at);
    }

    protected function saveItem(string $guid, string $title, string $link, string $description, string $publishedAt): bool
    {
        if($this->isItemExists($guid)) {
            /** @var RssFeedItem $item */
            $item = $this->oldItems[$guid];
            $hash = $this->getHash($title, $link, $description, $publishedAt);
            $oldHash = $this->getHashByItem($item);
            if($oldHash !== $hash) {
                $this->rssFeedItemRepository->updateRssFeedItem($item, $title, $link, $description, $publishedAt);
                $this->oldItems[$guid] = $item;
                event(new RssFeedItemChanged($item));
            }
        }
        else {
            $item = $this->rssFeedItemRepository->createRssFeedItem($guid, $title, $link, $description, $publishedAt);
            $this->oldItems[$guid] = $item;
            event(new RssFeedItemCreated($item));
        }

        return true;
    }

    protected function deleteItem(string $guid): bool
    {
        if(array_key_exists($guid, $this->oldItems)) {
            return false;
        }
        $item = $this->oldItems[$guid];
        $this->rssFeedItemRepository->deleteRssFeedItem($item);
        if(array_key_exists($guid, $this->oldItems)) {
            unset($this->oldItems[$guid]);
        }
        return true;
    }
}
