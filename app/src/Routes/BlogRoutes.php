<?php

namespace UserFrosting\Sprinkle\Blog\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Blog\Controller\AdminBlogPostController;
use UserFrosting\Sprinkle\Blog\Controller\AdminBlogsController;
use UserFrosting\Sprinkle\Blog\Controller\BlogController;
use UserFrosting\Sprinkle\Blog\Middlewares\BlogInjector;
use UserFrosting\Sprinkle\Blog\Middlewares\BlogPostInjector;

class BlogRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void 
    {
        $app->group('/blogs/b/{id}', function(RouteCollectorProxy $group) {
            $group->get('', [BlogController::class, 'pageBlog']);
        })->add(BlogInjector::class);
        
        $app->group('/admin/blogs', function(RouteCollectorProxy $group) {
            $group->get('', [AdminBlogsController::class, 'page']);

            $group->group('/b/{id}', function(RouteCollectorProxy $group) {
                $group->get('', [AdminBlogPostController::class, 'page']);
            })->add(BlogInjector::class);
        })->add(AuthGuard::class);
            
        $app->group('/api', function (RouteCollectorProxy $group) {
            $group->get('/blogs', [AdminBlogsController::class, 'getBlogs']);
            $group->post('/blogs', [AdminBlogsController::class, 'createBlog']);

            $group->group('/blogs/b/{id}', function (RouteCollectorProxy $group) {
                $group->get('', [AdminBlogsController::class, 'getBlog']);
                $group->put('', [AdminBlogsController::class, 'updateBlog']);
                $group->delete('', [AdminBlogsController::class, 'deleteBlog']);

                $group->get('/posts', [AdminBlogPostController::class, 'getPosts']);
                $group->post('/posts', [AdminBlogPostController::class, 'createPost']);
            })->add(BlogInjector::class);
            
            $group->group('/blog_posts/p/{id}', function (RouteCollectorProxy $group) {
                $group->put('', [AdminBlogPostController::class, 'editPost']);
                $group->delete('', [AdminBlogPostController::class, 'deletePost']);
            })->add(BlogPostInjector::class);
            
        })->add(AuthGuard::class);
        
        $app->group('/modals/blog', function (RouteCollectorProxy $group) {	
        
            $group->get('/create', [AdminBlogsController::class, 'getModalCreate']);
            $group->get('/edit', [AdminBlogsController::class, 'getModalEdit'])->add(BlogInjector::class);
            $group->get('/confirm-delete', [AdminBlogsController::class, 'getModalConfirmDelete'])->add(BlogInjector::class);
            
            $group->group('/post', function (RouteCollectorProxy $group) {
                $group->get('/create', [AdminBlogPostController::class, 'getModalPostCreate'])->add(BlogInjector::class);
                $group->get('/edit', [AdminBlogPostController::class, 'getModalPostEdit'])->add(BlogPostInjector::class);
                $group->get('/delete', [AdminBlogPostController::class, 'getModalPostDelete'])->add(BlogPostInjector::class);
            });
        })->add(AuthGuard::class);
    }
}