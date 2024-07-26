<?php
namespace UserFrosting\Sprinkle\Blog\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

class PostSprunje extends Sprunje
{
    protected $name = 'blog_posts';

	protected $sortable = [
		'title',
        'author',
		'last_updated_by',
		'created_at',
		'updated_by'    
    ];

    protected $filterable = [
		'title',
        'author',
		'last_updated_by',
		'created_at',
		'updated_by'
    ];
	
	protected $blog_slug;
	
	/**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        return $this->options['blog']->posts();
    }
}
