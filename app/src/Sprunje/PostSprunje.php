<?php
namespace UserFrosting\Sprinkle\Blog\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;

class PostSprunje extends Sprunje
{
    protected string $name = 'blog_posts';

	protected array $sortable = [
		'title',
        'author',
		'last_updated_by',
		'created_at',
		'updated_by'    
    ];

    protected array $filterable = [
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
        $instance = new BlogPost();

        // Alternatively, if you have defined a class mapping, you can use the classMapper:
        // $instance = $this->classMapper->createInstance('owl');

        return $instance->newQuery();
    }
}
