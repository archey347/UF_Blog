<?php

$app->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin')
    ->add('authGuard');
	
$app->post('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:createBlog')
    ->add('authGuard');
	

$app->get('/api/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlogs')
    ->add('authGuard');
	
$app->get('/modals/blog/create', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getModalCreate')
    ->add('authGuard');
