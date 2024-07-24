<?php

namespace UserFrosting\Sprinkle\Blog\Authorise;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

interface BlogAccessControlLayerInterface
{
    public function checkViewAccess(User $user, Blog $blog): bool;
    public function checkAdminAccess(User $user, Blog $blog): bool;
}