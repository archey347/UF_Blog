<?php

namespace UserFrosting\Sprinkle\Blog\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Blog extends Model
{
    protected $table = 'blogs';
    public $primaryKey = "id";

    protected $fillable = [
        'title',
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