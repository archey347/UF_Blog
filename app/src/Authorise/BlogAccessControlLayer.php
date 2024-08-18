<?php

namespace UserFrosting\Sprinkle\Blog\Authorise;

use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

/**
 * BlogAccessControlLayer Class
 * 
 * Implements BlogAccessControlLayerInterface. Used to check access to blogs.
 */
class BlogAccessControlLayer implements BlogAccessControlLayerInterface
{
    public function __construct(protected AuthorizationManager $acl)
    {
    }

    public function checkViewAccess(User $user, Blog $blog): bool
    {
        return true;
    }
    
    public function checkAdminAccess(User $user, Blog $blog): bool
    {
        return $this->acl->checkAccess($user, "uri_blog_manager");
    }
}