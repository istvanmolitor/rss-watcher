<?php

namespace Molitor\RssWatcher\Filament\Resources\RssFeedResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Molitor\RssWatcher\Filament\Resources\RssFeedResource;

class ListRssFeeds extends ListRecords
{
    protected static string $resource = RssFeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
