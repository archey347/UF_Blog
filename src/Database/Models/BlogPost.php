<?php

namespace UserFrosting\Sprinkle\Blog\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Account\Database\Models\User;

class BlogPost extends Model
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
        'post_og_image',
        'last_updates_by'
    ];
    
    protected $appends = [
        'author_user',
        'last_updates_by_user'
    ];

    public function getAuthorUserAttribute()
    {
        $user = User::find($this->author);
        return [
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "user_name" => $user->user_name,
        ];
    }
    
    public function getLastUpdatesByUserAttribute()
    {
        $user = User::find($this->last_updates_by);
        return [
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "user_name" => $user->user_name,
        ];
    }

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;
    
}