<?php

namespace Molitor\RssWatcher\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'enabled',
        'name',
        'url',
        'last_fetched_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RssFeedItem::class);
    }
}
