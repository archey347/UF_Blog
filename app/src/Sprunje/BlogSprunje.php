<?php
namespace UserFrosting\Sprinkle\Blog\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

class BlogSprunje extends Sprunje
{
    protected string $name = 'blogs';

	protected array $sortable = [
        'slug',
        'title'
    ];

    protected array $filterable = [
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