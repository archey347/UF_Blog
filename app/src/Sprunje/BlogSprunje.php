<?php
namespace UserFrosting\Sprinkle\Blog\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

class BlogSprunje extends Sprunje
{
    protected $name = 'blogs';

	protected $sortable = [
        'slug',
        'title'
    ];

    protected $filterable = [
        'slug',
        'title',
		'read_permission',
		'write_permission'
    ];
	
    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        $instance = new Blog();

        // Alternatively, if you have defined a class mapping, you can use the classMapper:
        // $instance = $this->classMapper->createInstance('owl');

        return $instance->newQuery();
    }
}