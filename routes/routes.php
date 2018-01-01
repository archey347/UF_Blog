<?php

$app->get('/blogs', 'UserFrosting\Sprinkle\Blog\Controller\BlogController:displayBlogAdmin')
    ->add('authGuard');
    