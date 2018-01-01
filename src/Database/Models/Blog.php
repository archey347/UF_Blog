<?php

namespace UserFrosting\Sprinkle\Blog\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Blog extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'blogs';
    public $primaryKey = "id";

    protected $fillable = [
        'slug',
        'title',
        'origin'
    ];

    public function posts()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Blog\Database\Models\BlogPosts');
    }
    
    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = false;
}