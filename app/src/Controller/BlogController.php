<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use UserFrosting\Sprinkle\Blog\Database\Models\Blog;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;

class BlogController
{
    function __construct(
        protected Twig $view, 
        protected Authenticator $authenticator,
        protected BlogAccessControlLayerInterface $acl)
    {
    }

    function pageBlog(Blog $blog, Request $request, Response $response) {
        if (!$this->acl->checkViewAccess($this->authenticator->user(), $blog)) {
           throw new NotFoundException();
        }

		$data = [
            "blog" => $blog->toArray(),
            "posts" => BlogPost::where('blog_id', $blog->id)->orderBy('created_at', 'desc')->get()->toArray()
		];
			
		return $this->view->render($response, 'pages/blog-view.html.twig', $data);   
	}
}