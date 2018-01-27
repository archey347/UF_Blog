<?php

namespace UserFrosting\Sprinkle\Blog\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Blog\Sprunje\BlogSprunje;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;

class BlogController extends SimpleController
{
    public function displayBlogAdmin(Request $request, Response $response, $args)
    {
		
		$schema = new RequestSchema('schema://requests/create-blog.yaml');
		
		$validator = new JqueryValidationAdapter($schema, $this->ci->translator);
        return $this->ci->view->render($response, 'pages/blogs.html.twig', [
			'page' => [
				'validators' => [
					'createBlog' => $validator->rules()
				]
			]
		]);        
    }
    
    public function getBlogs(Request $request, Response $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_blog_manager')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new BlogSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);    
    }
    
    public function getModalCreate(Request $request, Response $response, $args) {
		$permissions = Permission::all();
		$permissionOptions = array();
		foreach($permissions as $permission) {
			$permissionOptions[$permission['slug']] = $permission['name']. " (".$permission['slug'].")";
		}
        return $this->ci->view->render($response, 'modals/blog.html.twig',
            [
                "form" =>
                [
                    "submit" => "Create Blog",
					"action" => "blogs",
					"method" => "POST"
                ]
            ]
        );    
    }
	
	public function createBlog(Request $request, Response $response, $args) {
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/create-blog.yaml');
		
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
		
		// Check if that blog name already exists
		$blogs = Blog::where('slug', $data['blog_slug'])->get();
		if ($blogs->count()) {
			$ms->addMessage('danger', "Blog '".$data['blog_slug']."' already exists.");
			return $response->withStatus(400);	
		}
		
		// Check that the permissions don't already exist
		$perms = Permission::where('slug', $data['read_permission'])->get();
		if ($blogs->count()) {
			$ms->addMessage('danger', "Read Permission '".$data['read_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			// If it doesn't exist, then create it
			$perm = new Permission;
			$perm->slug = $data['read_permission'];
			$perm->name = "View Blog '".$data['blog_slug']."'";
			$perm->conditions = "always()";
			$perm->description = "Gives read access to the '".$data['blog_slug']."' blog.";
			$perm->save();
		}
		
		$perms = Permission::where('slug', $data['write_permission'])->get();
		if ($blogs->count()) {
			$ms->addMessage('danger', "Write Permission '".$data['write_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			$perm = new Permission;
			$perm->slug = $data['read_permission'];
			$perm->name = "Edit Blog '".$data['blog_slug']."'";
			$perm->conditions = "always()";
			$perm->description = "Gives write access to the '".$data['blog_slug']."' blog.";
			$perm->save();
		}
		
		// Create Blog
		$blog = new Blog;
		
		$blog->title = $data['blog_name'];
		$blog->slug = $data['blog_slug'];
		$blog->read_permission = $data['read_permission'];
		$blog->write_permission = $data['write_permission'];
		
		$blog->save();
		
		$ms->addMessage('success', "Successfully added blog '".$data['blog_slug']."'.");
		
	}
    
}