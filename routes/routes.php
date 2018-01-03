<?php

$app->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin')
    ->add('authGuard');

$app->get('/api/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:getBlogs')
    ->add('authGuard');    