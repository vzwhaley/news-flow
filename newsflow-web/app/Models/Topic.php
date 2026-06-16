<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'query',
        'position',
        'last_refreshed_at',
        'last_new_articles_at',
    ];

    protected function casts(): array
    {
        return [
            'last_refreshed_at'    => 'datetime',
            'last_new_articles_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The topic's feed, ordered top-to-bottom (position 0 = freshest/top).
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('position');
    }

    /**
     * The effective search query — falls back to the display name.
     */
    public function searchQuery(): string
    {
        return $this->query ?: $this->name;
    }
}
