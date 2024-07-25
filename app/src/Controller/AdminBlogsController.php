<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Fortress\Adapter\FormValidationArrayAdapter;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Blog\Sprunje\BlogSprunje;
use UserFrosting\Sprinkle\Blog\Sprunje\PostSprunje;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\Transformer\RequestDataTransformer as TransformerRequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator as ValidatorServerSideValidator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;

class AdminBlogsController
{
	function __construct(
        protected Twig $view, 
        protected Authenticator $authenticator,
		protected AuthorizationManager $authorizer,
        protected BlogAccessControlLayerInterface $acl,
		protected FormValidationArrayAdapter $formValidationArrayAdapter,
		protected ValidatorServerSideValidator $validator,
        protected TransformerRequestDataTransformer $transformer,
        protected AlertStream $alerts,
		)
    {
    }

    public function page(Request $request, Response $response)
    {
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager') 
			&& !$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager_view')) {
			throw new NotFoundException();
		}

		$create_rules = $this->formValidationArrayAdapter->rules(
			new RequestSchema('schema://requests/create-blog.yaml')
		);

		$edit_rules = $this->formValidationArrayAdapter->rules(
			new RequestSchema('schema://requests/edit-blog.yaml')
		);
		
		return $this->view->render($response, 'pages/blogs.html.twig', [
			'page' => [
				'validators' => [
					'create_blog' => $create_rules,
					'edit_blog'   => $edit_rules,
				]
			]
		]);        
    }
    
    public function getBlogs(Request $request, Response $response)
    {	
        // GET parameters
        $params = $request->getQueryParams();

        // Access-controlled page
        if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager') 
			&& !$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager_view')) {
			throw new NotFoundException();
		}

        $sprunje = new BlogSprunje($params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);    
    }
    
    public function getModalCreate(Request $request, Response $response) 
	{
        if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}
		
        return $this->view->render($response, 'modals/blog.html.twig',
            [
                "form" =>
                [
                    "submit" => "Create Blog",
					"action" => "api/blogs",
					"method" => "POST",
					"id" => "create-blog"
                ]
            ]
        );    
    }
	
	public function getModalEdit(Blog $blog, Request $request, Response $response) 
	{
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}
		
		return $this->view->render($response, 'modals/blog.html.twig',
            [
                "form" =>
                [
                    "submit" => "Update",
					"action" => "api/blogs/b/". $blog->id,
					"method" => "PUT",
					"id" => "edit-blog"
                ],
				"blog" =>
				[
					"id" => $blog->id,
					"name" => $blog->title,
					"read_p" => $blog->read_permission,
					"write_p" => $blog->write_permission,
					"public" => $blog->public,
				]
            ]
        );    
    }

	public function getModalConfirmDelete(Blog $blog, Request $request, Response $response) {
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}

        return $this->view->render($response, 'modals/confirm-delete-blog.html.twig',
            [
				"form" =>
                [
					"action" => "api/blogs/b/" . $blog->id,
                ],
				"blog" =>
				[
					"id" => $blog->id,
					"name" => $blog->title,
					"read_p" => $blog->read_permission,
					"write_p" => $blog->write_permission,
				]
            ]
        );    
    }

	public function createBlog(Request $request, Response $response) 
	{
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}
		
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/create-blog.yaml');
		
		// Whitelist and set parameter defaults
		$data = $this->transformer->transform($schema, $params);
		
		$errors = $this->validator->validate($schema, $data);
		
		// Add error messages and halt if validation failed
		if (count($errors) > 0) {
			$e = new ValidationException();
			$e->addErrors($errors);

			throw $e;
		}
						
		// Create Blog
		$blog = new Blog;
		
		$blog->title = $data['blog_name'];
		$blog->read_permission = $data['public'] == 1 ? "" : $data['read_permission'];
		$blog->write_permission = $data['write_permission'];
		$blog->public = $data['public'] == 1;
		$blog->save();
		
		$this->alerts->addMessage('success', "Successfully added blog '".$data['blog_slug']."'.");
		
		$payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
	
	public function updateBlog(Blog $blog, Request $request, Response $response) 
	{
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}

		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/edit-blog.yaml');
		
		// Whitelist and set parameter defaults
		$data = $this->transformer->transform($schema, $params);
		
		$errors = $this->validator->validate($schema, $data);
		
		// Add error messages and halt if validation failed
		if (count($errors) > 0) {
			$e = new ValidationException();
			$e->addErrors($errors);

			throw $e;
		}
		
		$blog->title = $data['blog_name'];

		$blog->read_permission = $data['public'] == 1 ? "" : $data['read_permission'];
		$blog->write_permission = $data['write_permission'];
		
		$blog->public = $data['public'];
		
		$blog->save();
		
		$this->alerts->addMessage('success', "Successfully updated blog '".$data['blog_slug']."'.");

		$payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
	
	public function deleteBlog(Blog $blog, Request $request, Response $response) {
		if(!$this->authorizer->checkAccess($this->authenticator->user(), 'uri_blog_manager')) {
			throw new NotFoundException();
		}

		// Get submitted data
		$params = $request->getParsedBody();
	
		$blog->delete();
		
		$this->alerts->addMessage('success', "Successfully deleted blog '{$blog->name}'.");
	}
	
	function getBlog(Blog $blog, Request $request, Response $response) {
		if(!$this->acl->checkViewAccess($this->authenticator->user(), $blog)) {
			throw new NotFoundException();
		}
		
		$blog->load('posts');
		
		$payload = json_encode($blog->toArray(), JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
	}
}

