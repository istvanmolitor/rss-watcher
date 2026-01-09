<?php

namespace Molitor\RssWatcher\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Molitor\RssWatcher\Filament\Resources\RssFeedResource\Pages;
use Molitor\RssWatcher\Models\RssFeed;
use Molitor\RssWatcher\Services\RssWatcherService;
use Illuminate\Database\Eloquent\Collection;

class RssFeedResource extends Resource
{
    protected static ?string $model = RssFeed::class;

    public static function getNavigationLabel(): string
    {
        return __('rss-watcher::common.rss_feed_navigation_label');
    }

    public static function getPluralLabel(): ?string
    {
        return __('rss-watcher::common.rss_feed_plural_label');
    }

    public static function getModelLabel(): string
    {
        return __('rss-watcher::common.rss_feed_model_label');
    }

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-rss';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('rss-watcher::common.rss_feed_field_name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->label(__('rss-watcher::common.rss_feed_field_url'))
                    ->required()
                    ->url()
                    ->maxLength(255),
                Forms\Components\Toggle::make('enabled')
                    ->label(__('rss-watcher::common.rss_feed_field_enabled'))
                    ->default(true),
                Forms\Components\DateTimePicker::make('last_fetched_at')
                    ->label(__('rss-watcher::common.rss_feed_field_last_fetched_at'))
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('rss-watcher::common.rss_feed_field_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('rss-watcher::common.rss_feed_field_url'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('enabled')
                    ->label(__('rss-watcher::common.rss_feed_field_enabled'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_fetched_at')
                    ->label(__('rss-watcher::common.rss_feed_field_last_fetched_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('refresh_bulk')
                        ->label(__('rss-watcher::common.rss_feed_action_refresh'))
                        ->icon('heroicon-o-arrow-path')
                        ->action(fn (Collection $records, RssWatcherService $service) => $records->each(fn (RssFeed $record) => $service->queueFetchFeed($record)))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('rss-watcher::common.rss_feed_action_refresh_success')),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRssFeeds::route('/'),
            'create' => Pages\CreateRssFeed::route('/create'),
            'edit' => Pages\EditRssFeed::route('/{record}/edit'),
        ];
    }
}
