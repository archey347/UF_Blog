<?php

namespace UserFrosting\Sprinkle\Blog\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class BlogPosts extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'blog_posts';
    public $primaryKey = "id";

    protected $fillable = [
        'blog_id',
        'title',
        'content',
        'author',
        'last_updates_by'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;
}