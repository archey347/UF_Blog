<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace Spanner\Clubs\Middlewares;

use UserFrosting\Sprinkle\Blog\Database\Models\Blog;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\Core\Middlewares\Injector\AbstractInjector;

/**
 * Route middleware to inject group when it's slug is passed via placeholder in the URL or request query.
 */
class BlogInjector extends AbstractInjector
{
    protected string $placeholder = 'id';

    // Middleware attribute name.
    protected string $attribute = 'blog';

    /**
     * Returns the club's instance.
     *
     * @param string|null $slug
     *
     * @return GroupInterface
     */
    protected function getInstance(?string $id): Blog
    {
        if ($id === null || ($club = Blog::find($id)) === null) {
            throw new NotFoundException();
        }

        // @phpstan-ignore-next-line Role Interface is a model
        return $club;
    }
}