<?php
// These need changing to REST standards
$app->group('/blogs', function() {
	$this->get('', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin');
	
	$this->get('/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getSingleBlogAdmin');
	
	
	
})->add('authGuard');	

	
$app->post('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:createBlog')
    ->add('authGuard');

$app->put('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:updateBlog')
    ->add('authGuard');
	
$app->delete('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:deleteBlog')
    ->add('authGuard');

$app->group('/api', function () {
	$this->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlogs');
	
	$this->get('/blogs/b/{blog_slug}', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlog');
	
	$this->get('/blogs/b/{blog_slug}/posts', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getPosts');	
})->add('authGuard');

	
$app->get('/modals/blog/create', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalCreate')
    ->add('authGuard');

$app->get('/modals/blog/edit', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalEdit')
    ->add('authGuard');

$app->get('/modals/blog/confirm-delete', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalConfirmDelete')
    ->add('authGuard');