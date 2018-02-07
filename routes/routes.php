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