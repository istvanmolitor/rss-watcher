<?php

namespace Tests\Feature\Services;

use App\Services\RssWatcherCmsPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Molitor\Cms\Models\PostGroup;
use Molitor\Language\Models\Language;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Models\RssFeedItem;
use Tests\TestCase;

class RssWatcherCmsPageServiceTest extends TestCase
{
    use RefreshDatabase;

    private RssWatcherCmsPostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RssWatcherCmsPostService::class);

        // Create a default language
        Language::query()->create([
            'code' => 'hu',
            'enabled' => true,
        ]);
    }

    public function test_creates_post_with_content_and_metadata(): void
    {
        $rssFeed = RssFeed::query()->create([
            'enabled' => true,
            'name' => 'Test Feed',
            'url' => 'https://example.com/rss',
        ]);

        $rssFeedItem = RssFeedItem::query()->create([
            'rss_feed_id' => $rssFeed->id,
            'guid' => 'test-guid-123',
            'title' => 'Test Article',
            'link' => 'https://example.com/article',
            'description' => 'Test description',
            'image' => 'https://example.com/image.jpg',
            'published_at' => now(),
        ]);

        $post = $this->service->createOrUpdateFromRssItem($rssFeedItem);

        $this->assertSame('hu', Language::query()->where('enabled', true)->first()->code);
        $this->assertSame('Test Article', $post->title);
        $this->assertSame('test-article', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertSame('https://example.com/image.jpg', $post->main_image_url);

        $postGroup = PostGroup::query()->first();
        $this->assertNotNull($postGroup);
        $this->assertSame('example.com', $postGroup->name);
        $this->assertSame('example-com', $postGroup->slug);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);

        $this->assertDatabaseHas('post_groups', [
            'id' => $postGroup->id,
            'name' => 'example.com',
            'slug' => 'example-com',
        ]);

        $this->assertDatabaseHas('post_post_groups', [
            'post_id' => $post->id,
            'post_group_id' => $postGroup->id,
        ]);

        $this->assertDatabaseHas('post_meta', [
            'post_id' => $post->id,
            'name' => 'source_link',
            'meta_data' => 'https://example.com/article',
        ]);

        $this->assertDatabaseHas('post_meta', [
            'post_id' => $post->id,
            'name' => 'rss_feed_item_id',
            'meta_data' => (string) $rssFeedItem->id,
        ]);
    }

    public function test_updates_existing_post_for_same_rss_feed_item(): void
    {
        $rssFeed = RssFeed::query()->create([
            'enabled' => true,
            'name' => 'Test Feed',
            'url' => 'https://example.com/rss',
        ]);

        $rssFeedItem = RssFeedItem::query()->create([
            'rss_feed_id' => $rssFeed->id,
            'guid' => 'test-guid-456',
            'title' => 'Original title',
            'link' => 'https://example.com/original',
            'description' => 'Original description',
            'published_at' => now(),
        ]);

        $firstPost = $this->service->createOrUpdateFromRssItem($rssFeedItem);

        $rssFeedItem->update([
            'title' => 'Updated title',
            'description' => 'Updated description',
            'link' => 'https://example.com/updated',
        ]);

        $updatedPost = $this->service->createOrUpdateFromRssItem($rssFeedItem->fresh());

        $postGroup = PostGroup::query()->first();

        $this->assertSame($firstPost->id, $updatedPost->id);
        $this->assertSame('Updated title', $updatedPost->title);
        $this->assertSame('Updated description', $updatedPost->lead);
        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseCount('post_groups', 1);
        $this->assertNotNull($postGroup);
        $this->assertDatabaseHas('post_post_groups', [
            'post_id' => $updatedPost->id,
            'post_group_id' => $postGroup->id,
        ]);
    }
}

