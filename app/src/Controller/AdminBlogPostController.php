<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Fortress\Adapter\FormValidationArrayAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;
use UserFrosting\Sprinkle\Blog\Sprunje\PostSprunje;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;

class AdminBlogPostController
{
    function __construct(
        protected Twig $view, 
        protected Authenticator $authenticator,
        protected BlogAccessControlLayerInterface $acl,
		protected FormValidationArrayAdapter $formValidationArrayAdapter,
		protected ServerSideValidator $validator,
        protected RequestDataTransformer $transformer,
        protected AlertStream $alerts)
    {
    }

    function page(Blog $blog, Request $request, Response $response) 
	{
		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
		$schema = new RequestSchema('schema://requests/post.yaml');
		$rules = $this->formValidationArrayAdapter->rules($schema);
		
		return $this->view->render($response, 'pages/blog.html.twig', [
			'page' => [
				'validators' => [
					'post' => $rules
				],
				"blog" => $blog->toArray()
			]
		]);   
			
	}
	
	function getPosts(Blog $blog, Request $request, Response $response) {
		
		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
		$params = $request->getQueryParams();
		$params['blog_id'] = $blog;

        $sprunje = new PostSprunje($params);

		$sprunje->extendQuery(function ($query) use ($blog) {
			return $query->where('blog_id', $blog->id);
		});

        return $sprunje->toResponse($response); 
	}
	
	public function getModalPostCreate(Blog $blog, Request $request, Response $response) 
	{	
		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}

		return $this->view->render($response, 'modals/blog-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Create Post",
					"action" => "api/blogs/b/{$blog->id}/posts",
					"method" => "POST",
					"id" => "create-post"
                ]
            ]
        );    
    }
	
	public function createPost(Blog $blog, Request $request, Response $response) 
	{
		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		// Get submitted data
	
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/post.yaml');
		
		// Whitelist and set parameter defaults
		$data = $this->transformer->transform($schema, $params);
		
		$errors = $this->validator->validate($schema, $data);
		
		if(count($errors) > 0) {
			$e = new ValidationException();
			$e->addErrors($errors);

			throw $e;
		}
		
		$blog->posts()->create([
			"title" => $params['post_title'],
			"content" => $params['post_content'],
			"author" => $this->authenticator->user()->id,
			"last_updates_by" => $this->authenticator->user()->id,
		]);
		
		$this->alerts->addMessage('success', "Blog post successfully created.");

		$payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
	
	public function getModalPostEdit(BlogPost $blog_post, Request $request, Response $response) 
	{	
		$blog_post->load('blog');
		$blog = $blog_post->blog;

		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
		return $this->view->render($response, 'modals/blog-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Edit Post",
					"action" => "api/blog_posts/p/{$blog_post->id}",
					"method" => "PUT",
					"id" => "edit-post"
                ],
				"post" => $blog_post->toArray()
            ]
        );    
    }
	
	public function editPost(BlogPost $blog_post, Request $request, Response $response) 
	{
		$blog_post->load('blog');
		$blog = $blog_post->blog;

		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/post.yaml');
		
		// Whitelist and set parameter defaults
		$data = $this->transformer->transform($schema, $params);
		
		$errors = $this->validator->validate($schema, $data);
		
		if(count($errors) > 0) {
			$e = new ValidationException();
			$e->addErrors($errors);

			throw $e;
		}
		
		$blog_post->title = $params['post_title'];
		$blog_post->content = $params['post_content'];
		$blog_post->last_updates_by = $this->authenticator->user()->id;
		$blog_post->save();
		
		$this->alerts->addMessage('success', "Blog post successfully updated.");

		$payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
	
	public function getModalPostDelete(BlogPost $blog_post, Request $request, Response $response) 
	{
		$blog_post->load('blog');
		$blog = $blog_post->blog;

		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
        return $this->view->render($response, 'modals/confirm-delete-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Delete Post",
					"action" => "api/blog_posts/p/{$blog_post->id}",
					"method" => "DELETE",
                ],
				"post" => $blog_post->toArray(),
            ]
        );    
    }
	
	function deletePost(BlogPost $blog_post, Request $request, Response $response) {
		$blog_post->load('blog');
		$blog = $blog_post->blog;

		if(!$this->acl->checkAdminAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}

		$blog_post->delete();
		
		$this->alerts->addMessage('success', "Successfully deleted post.");

		$payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
}