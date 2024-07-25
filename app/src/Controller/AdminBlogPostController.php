<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface;

class AdminBlogPostController
{
    function __construct(
        protected Twig $view, 
        protected Authenticator $authenticator,
        protected BlogAccessControlLayerInterface $acl)
    {
    }

    function getSingleBlogAdmin(Request $request, Response $response, $args) {
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		$this->checkAccess($blog->read_permission);
		
		if ($blog == null) {
			throw new NotFoundException($request, $response);	
		}
		
		$create_post_schema = new RequestSchema('schema://requests/create-post.yaml');
		$create_post_validator = new JqueryValidationAdapter($create_post_schema, $this->ci->translator);
		
		$edit_post_schema = new RequestSchema('schema://requests/edit-post.yaml');
		$edit_post_validator = new JqueryValidationAdapter($edit_post_schema, $this->ci->translator);
		
		
		return $this->ci->view->render($response, 'pages/blog.html.twig', [
			'blog' => $blog->toArray(),
			'page' => [
				'validators' => [
					'postCreate' => $create_post_validator->rules(),
					'postEdit' => $edit_post_validator->rules()
				],
				"blog" => $blog->toArray()
			]
		]);   
			
	}
	
	function getPosts(Request $request, Response $response, $args) {
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		$this->checkAccess($blog->read_permission);
		
		if ($blog == null) {
			throw new NotFoundException($request, $response);	
		}
		
		// GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new PostSprunje($classMapper, $params, $args['blog_slug']);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response); 
	}
	
	public function getModalPostCreate(Request $request, Response $response, $args) {
		
		$blog_slug = $request->getQueryParam('blog_slug');
		
		$ms = $this->ci->alerts;
		
		if($blog_slug == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
        $blog = Blog::where('slug', $blog_slug)->first();
		
		if($blog == null) {
			$ms->addMessage('danger', "Blog '$blog_slug' doesn't exist.");
			return $response->withStatus(400);
		}
		
		$this->checkAccess($blog->write_access);
		
		return $this->ci->view->render($response, 'modals/blog-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Create Post",
					"action" => "api/blogs/b/$blog_slug/posts",
					"method" => "POST",
					"id" => "create-post"
                ]
            ]
        );    
    }
	
	public function createPost(Request $request, Response $response, $args) {
		
		
		$ms = $this->ci->alerts;
		
		if($args['blog_slug'] == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/create-post.yaml');
		
		// Whitelist and set parameter defaults
		$transformer = new RequestDataTransformer($schema);
		$data = $transformer->transform($params);
		
		/** @var UserFrosting\Sprinkle\Core\MessageStream $ms */
		$ms = $this->ci->alerts;
		
		$validator = new ServerSideValidator($schema, $this->ci->translator);
		
		// Add error messages and halt if validation failed
		if (!$validator->validate($data)) {
			$ms->addValidationErrors($validator);
			return $response->withStatus(400);
		}
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		$this->checkAccess($blog->write_permission);
		
		if($blog == null) {
			$ms->addMessage('danger', $args['blog_slug']." doesn't exist.");
			return $response->withStatus(400);	
		}
		
		$blog->posts()->create([
			"title" => $params['post_title'],
			"content" => $params['post_content'],
			"author" => $this->ci->currentUser->id,
			"last_updates_by" => $this->ci->currentUser->id
		]);
		
		$ms->addMessage('success', "Blog post successfully created.");
	}
	
	public function getModalPostEdit(Request $request, Response $response, $args) {
		
		$blog_slug = $request->getQueryParam('slug');
		$post_id = $request->getQueryParam('id');
		
		
		$ms = $this->ci->alerts;
		
		if($blog_slug == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		if($post_id == null) {
			$ms->addMessage('danger', "No post assigned to edit.");
			return $response->withStatus(422);
		}
		
		$blog = Blog::where('slug', $blog_slug)->first();
		
		$this->checkAccess($blog->write_permission);

		if(!$blog->count()) {
			$ms->addMessage('danger', "Blog with slug '{$blog_slug}' not found");
			return $response->withStatus(404);
		}
		
		$post = BlogPost::find($post_id);
		
		if($post == null) {
			$ms->addMessage('danger', "Post Not Found");
			return $response->withStatus(404);
		}
		
        return $this->ci->view->render($response, 'modals/blog-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Edit Post",
					"action" => "api/blogs/b/$blog_slug/posts/p/$post_id",
					"method" => "PUT",
					"id" => "edit-post"
                ],
				"post" =>
				[
					"title" => $post->title,
					"content" => $post->content
				]
            ]
        );    
    }
	
	public function editPost(Request $request, Response $response, $args) {
		
		
		$ms = $this->ci->alerts;
		
		if($args['blog_slug'] == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		if($args['post_id'] == null) {
			$ms->addMessage('danger', "No post assigned to edit.");
			return $response->withStatus(422);
		}
		
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/edit-post.yaml');
		
		// Whitelist and set parameter defaults
		$transformer = new RequestDataTransformer($schema);
		$data = $transformer->transform($params);
		
		/** @var UserFrosting\Sprinkle\Core\MessageStream $ms */
		$ms = $this->ci->alerts;
		
		$validator = new ServerSideValidator($schema, $this->ci->translator);
		
		// Add error messages and halt if validation failed
		if (!$validator->validate($data)) {
			$ms->addValidationErrors($validator);
			return $response->withStatus(400);
		}
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		$this->checkAccess($blog->write_permission);
		
		if($blog == null) {
			$ms->addMessage('danger', $args['blog_slug']." doesn't exist.");
			return $response->withStatus(400);	
		}
		
		$post = BlogPost::find($args['post_id']);
		
		if($post == null) {
			$ms->addMessage('danger', "Post Not Found");
			return $response->withStatus(404);
		}
		
		$post->title = $params['post_title'];
		$post->content = $params['post_content'];
		
		$post->last_updates_by = $this->ci->currentUser->id;
		
		$post->save();
		
		$ms->addMessage('success', "Blog post successfully updated.");
	}
	
	public function getModalPostDelete(Request $request, Response $response, $args) {
		
		$blog_slug = $request->getQueryParam('slug');
		$post_id = $request->getQueryParam('id');
		
		
		$ms = $this->ci->alerts;
		
		if($blog_slug == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		if($post_id == null) {
			$ms->addMessage('danger', "No post assigned to edit.");
			return $response->withStatus(422);
		}
		
		$blog = Blog::where('slug', $blog_slug)->first();
		
		$this->checkAccess($blog->write_access);

		if(!$blog->count()) {
			$ms->addMessage('danger', "Blog with slug '{$blog_slug}' not found");
			return $response->withStatus(404);
		}
		
		$post = BlogPost::find($post_id);
		
		if($post == null) {
			$ms->addMessage('danger', "Post Not Found");
			return $response->withStatus(404);
		}
		
        return $this->ci->view->render($response, 'modals/confirm-delete-post.html.twig',
            [
                "form" =>
                [
                    "submit" => "Delete Post",
					"action" => "api/blogs/b/$blog_slug/posts/p/$post_id",
					"method" => "DELETE",
                ],
				"post" =>
				[
					"title" => $post->title,
					"content" => $post->content
				]
            ]
        );    
    }
	
	function deletePost(Request $request, Response $response, $args) {
		$ms = $this->ci->alerts;
		
		if($args['blog_slug'] == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		$this->checkAccess($blog->write_permission);
		
		if($args['post_id'] == null) {
			$ms->addMessage('danger', "No post assigned to delete.");
			return $response->withStatus(422);
		}
		
		$post = BlogPost::find($args['post_id']);
		
		if($post == null) {
			$ms->addMessage('danger', "Post not found.");
			return $response->withStatus(404);	
		}
		
		$post->delete();
		
		$ms->addMessage('success', "Successfully deleted post.");
		
	}
}