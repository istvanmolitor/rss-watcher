<?php

declare(strict_types=1);

namespace Molitor\RssWatcher\DataTables;

use Molitor\Admin\DataTables\DataTable;
use Molitor\RssWatcher\Http\Resources\RssFeedResource;
use Molitor\RssWatcher\Models\RssFeed;

class RssFeedDataTable extends DataTable
{
    protected function getModelClass(): string
    {
        return RssFeed::class;
    }

    protected function getResourceClass(): string
    {
        return RssFeedResource::class;
    }

    protected function getDefaultSort(): string
    {
        return 'id';
    }

    protected function getDefaultDirection(): string
    {
        return 'desc';
    }

    protected function getPerPage(): int
    {
        return $this->request->integer('per_page', 15);
    }

    protected function getSearchPlaceholder(): string
    {
        return 'Keresés RSS feed alapján...';
    }

    protected function initColumns(): void
    {
        $this->addColumn('name')
            ->setLabel('Név')
            ->setSearchable()
            ->setOrderable();

        $this->addColumn('url')
            ->setLabel('URL')
            ->setSearchable();

        $this->addColumn('enabled')
            ->setLabel('Állapot')
            ->setOrderable();

        $this->addColumn('last_fetched_at')
            ->setLabel('Utolsó lekérés')
            ->setOrderable();
    }
}
