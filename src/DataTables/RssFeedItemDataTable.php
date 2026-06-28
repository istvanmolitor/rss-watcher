<?php

declare(strict_types=1);

namespace Molitor\RssWatcher\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Molitor\Admin\DataTables\DataTable;
use Molitor\RssWatcher\Http\Resources\RssFeedItemResource;
use Molitor\RssWatcher\Models\RssFeedItem;

class RssFeedItemDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return RssFeedItem::class;
    }

    protected function getResourceClass(): string
    {
        return RssFeedItemResource::class;
    }

    protected function getDefaultSort(): string
    {
        return 'published_at';
    }

    protected function getDefaultDirection(): string
    {
        return 'desc';
    }

    protected function getPerPage(): int
    {
        return $this->request->integer('per_page', 15);
    }

    protected function initColumns(): void
    {
        $this->addColumn('title')
            ->setLabel('Cím')
            ->setSearchable()
            ->setOrderable();

        $this->addColumn('feed')
            ->setLabel('Feed');

        $this->addColumn('published_at')
            ->setLabel('Publikálva')
            ->setOrderable();
    }

    public function query(Builder $query): Builder
    {
        $query->with('feed');

        if ($feedId = $this->request->input('feed_id')) {
            $query->where('rss_feed_id', $feedId);
        }

        return $query;
    }
}
