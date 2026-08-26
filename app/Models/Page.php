<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content_json',
        'content_language',
        'enable_auto_numbering',
        'show_in_header',
        'show_in_footer',
        'summary',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'enable_auto_numbering' => 'boolean',
        'show_in_header' => 'boolean',
        'show_in_footer' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'is_published' => false,
    ];
}
