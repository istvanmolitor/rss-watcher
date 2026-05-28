<?php

namespace Molitor\RssWatcher\Tests\Feature;

use Molitor\RssWatcher\Providers\RssWatcherServiceProvider;
use Tests\TestCase;

class PackageSmokeTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $this->assertTrue(class_exists(RssWatcherServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(RssWatcherServiceProvider::class));
    }
}

