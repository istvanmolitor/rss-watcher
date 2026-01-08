<?php

namespace Molitor\RssWatcher\Filament\Resources\RssFeedResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Molitor\RssWatcher\Filament\Resources\RssFeedResource;

class EditRssFeed extends EditRecord
{
    protected static string $resource = RssFeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
