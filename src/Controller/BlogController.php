<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;
use UserFrosting\Sprinkle\Core\Facades\Debug;

class BlogController extends SimpleController
{
    public function displayBlogAdmin(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'pages/blogs.html.twig');        
    }
}