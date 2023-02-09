<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

$app->group('/blogs', function () {
    $this->get('', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin');
})->add('authGuard');

$app->get('/blogs/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getSingleBlogAdmin')
    ->add('authGuard');

$app->get('/blogs/b/{blog_slug}/view', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:genBlog');

$app->get('/blogs/b/{blog_slug}/view/{post_id}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:genSingleBlogPost');

$app->group('/api', function () {
    $this->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlogs');

    $this->post('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:createBlog');

    $this->put('/blogs/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:updateBlog');

    $this->delete('/blogs/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:deleteBlog');

    $this->get('/blogs/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlog');

    $this->get('/blogs/b/{blog_slug}/posts', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getPosts');

    $this->post('/blogs/b/{blog_slug}/posts', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:createPost');

    $this->put('/blogs/b/{blog_slug}/posts/p/{post_id}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:editPost');

    $this->delete('/blogs/b/{blog_slug}/posts/p/{post_id}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:deletePost');
})->add('authGuard');

$app->group('/modals/blog', function () {
    $this->get('/create', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalCreate');

    $this->get('/edit', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalEdit');

    $this->get('/confirm-delete', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalConfirmDelete');

    $this->group('/post', function () {
        $this->get('/create', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalPostCreate');

        $this->get('/edit', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalPostEdit');

        $this->get('/delete', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalPostDelete');
    });
})->add('authGuard');
