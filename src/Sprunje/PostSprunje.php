<?php
namespace UserFrosting\Sprinkle\Blog\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

class PostSprunje extends Sprunje
{
    protected $name = 'blogposts';

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
	
	public function __construct(ClassMapper $classMapper, array $options, string $blog_slug) {
		$this->blog_slug = $blog_slug;
		parent::__construct($classMapper, $options);
	}
	
    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
		
        $instance = Blog::where('slug', $this->blog_slug)->first();

		$instance = $instance->posts();
		
        return $instance->newQuery();
    }
}