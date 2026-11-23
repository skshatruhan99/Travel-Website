<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalPost extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content', 'category', 'featured_image', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }
}
