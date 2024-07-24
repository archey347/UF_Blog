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
        if($blog->public) {
            return true;
        }
        
        if($this->acl->checkAccess($user, $blog->read_permission)) {
            return true;
        }

        return false;
    }
    
    public function checkAdminAccess(User $user, Blog $blog): bool
    {
        if($this->acl->checkAccess($user, $blog->write_permission)) {
            return true;
        }

        return false;
    }
}