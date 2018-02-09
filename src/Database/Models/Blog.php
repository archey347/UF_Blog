<?php

namespace UserFrosting\Sprinkle\Blog\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Blog extends Model
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Blog\Database\Migrations\v000\BlogPost',
    ];
    
    
    protected $table = 'blogs';
    public $primaryKey = "id";

    protected $fillable = [
        'slug',
        'title',
        'read_permission',
        'write_permission',
        'public'
    ];

    public function posts()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Blog\Database\Models\BlogPost');
    }
    
    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = false;
}