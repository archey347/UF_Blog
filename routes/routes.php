<?php

$app->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin')
    ->add('authGuard');
	
$app->post('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:createBlog')
    ->add('authGuard');

$app->put('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:updateBlog')
    ->add('authGuard');
	
$app->delete('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:deleteBlog')
    ->add('authGuard');

	
$app->get('/api/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlogs')
    ->add('authGuard');
	
$app->get('/modals/blog/create', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalCreate')
    ->add('authGuard');

$app->get('/modals/blog/edit', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalEdit')
    ->add('authGuard');

$app->get('/modals/blog/confirm-delete', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalConfirmDelete')
    ->add('authGuard');