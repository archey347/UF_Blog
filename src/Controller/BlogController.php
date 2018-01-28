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
		
		$create_schema = new RequestSchema('schema://requests/create-blog.yaml');
		$create_validator = new JqueryValidationAdapter($create_schema, $this->ci->translator);
		
        $edit_schema = new RequestSchema('schema://requests/edit-blog.yaml');
		$edit_validator = new JqueryValidationAdapter($edit_schema, $this->ci->translator);
		
		$delete_schema = new RequestSchema('schema://requests/confirm-delete-blog.yaml');
		$delete_validator = new JqueryValidationAdapter($delete_schema, $this->ci->translator);
		
		return $this->ci->view->render($response, 'pages/blogs.html.twig', [
			'page' => [
				'validators' => [
					'createBlog' => $create_validator->rules(),
					'editBlog' => $edit_validator->rules(),
					'confirmDeleteBlog' => $delete_validator->rules()
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
        return $this->ci->view->render($response, 'modals/blog.html.twig',
            [
                "form" =>
                [
                    "submit" => "Create Blog",
					"action" => "blogs",
					"method" => "POST",
					"id" => "create-blog"
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
			$perm->slug = $data['write_permission'];
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
	
	public function getModalEdit(Request $request, Response $response, $args) {
		
		$blog_slug = $request->getQueryParam('slug');
		
		$ms = $this->ci->alerts;
		
		if($blog_slug == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		$blog = Blog::where('slug', $blog_slug)->first();

		if(!$blog->count()) {
			$ms->addMessage('danger', "Blog with slug '{$blog_slug}' not found");
			return $response->withStatus(404);
		}
		
        return $this->ci->view->render($response, 'modals/blog.html.twig',
            [
                "form" =>
                [
                    "submit" => "Update",
					"action" => "blogs",
					"method" => "PUT",
					"id" => "edit-blog"
                ],
				"blog" =>
				[
					"id" => $blog->id,
					"slug" => $blog->slug,
					"name" => $blog->title,
					"read_p" => $blog->read_permission,
					"write_p" => $blog->write_permission,
					"read_id" => Permission::where('slug', $blog->read_permission)->first()->id,
					"write_id" => Permission::where('slug', $blog->write_permission)->first()->id
				]
            ]
        );    
    }
	
	public function updateBlog(Request $request, Response $response, $args) {
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/edit-blog.yaml');
		
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
		
		// Check to make sure that the sent id is valid
		$current_blog = Blog::find($data['blog_id']);
		if ($current_blog == null) {
			$ms->addMessage('danger', "Blog not found.");
			return $response->withStatus(400);	
		}
		
		// Check if that blog name already exists and is not the blog currently being modified
		$blog = Blog::where('slug', $data['blog_slug'])->first();
		if ($blog != null && $blog->id != $current_blog->id) {
			$ms->addMessage('danger', "Blog '".$data['blog_slug']."' already exists.");
			return $response->withStatus(400);	
		}
		
		// Check that the permissions don't already exist
		$perm = Permission::where('slug', $data['read_permission'])->first();
		if ($perm != null && $perm->id != $data['read_id']) {
			$ms->addMessage('danger', "Read Permission '".$data['read_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			if($perm == null) {
				$perm = Permission::find($data['read_id']);	
			}
			$perm->slug = $data['read_permission'];
			$perm->name = "View Blog '".$data['blog_slug']."'";
			$perm->conditions = "always()";
			$perm->description = "Gives read access to the '".$data['blog_slug']."' blog.";
			$perm->save();
		}
		
		// Check that the permissions don't already exist
		$perm = Permission::where('slug', $data['write_permission'])->first();
		if ($perm != null && $perm->id != $data['write_id']) {
			$ms->addMessage('danger', "Write Permission '".$data['write_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			if($perm == null) {
				$perm = Permission::find($data['write_id']);
			}
			$perm->slug = $data['write_permission'];
			$perm->name = "Edit Blog '".$data['blog_slug']."'";
			$perm->conditions = "always()";
			$perm->description = "Gives write access to the '".$data['blog_slug']."' blog.";
			$perm->save();	
		}
		
		
		
		$current_blog->title = $data['blog_name'];
		$current_blog->slug = $data['blog_slug'];
		$current_blog->read_permission = $data['read_permission'];
		$current_blog->write_permission = $data['write_permission'];
		
		$current_blog->save();
		
		$ms->addMessage('success', "Successfully updated blog '".$data['blog_slug']."'.");
		
	}
    
	public function getModalConfirmDelete(Request $request, Response $response, $args) {
		
		$blog_slug = $request->getQueryParam('slug');
		
		$ms = $this->ci->alerts;
		
		if($blog_slug == null) {
			$ms->addMessage('danger', "No blog assigned to edit.");
			return $response->withStatus(422);
		}
		
		$blog = Blog::where('slug', $blog_slug)->first();

		if(!$blog->count()) {
			$ms->addMessage('danger', "Blog with slug '{$blog_slug}' not found");
			return $response->withStatus(404);
		}
		
        return $this->ci->view->render($response, 'modals/confirm-delete-blog.html.twig',
            [
				"form" =>
                [
					"action" => "blogs",
                ],
				"blog" =>
				[
					"id" => $blog->id,
					"slug" => $blog->slug,
					"name" => $blog->title,
					"read_p" => $blog->read_permission,
					"write_p" => $blog->write_permission,
					"read_id" => Permission::where('slug', $blog->read_permission)->first()->id,
					"write_id" => Permission::where('slug', $blog->write_permission)->first()->id
				]
            ]
        );    
    }
	
	public function deleteBlog(Request $request, Response $response, $args) {
		
		// Get submitted data
		$params = $request->getParsedBody();
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/confirm-delete-blog.yaml');
		
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
		$blog = Blog::find($data['blog_id']);
		if ($blog == null) {
			$ms->addMessage('danger', "Blog selected to delete doesn't exist.");
			return $response->withStatus(400);	
		}
		
		// Check that the permission exists
		$perm = Permission::where('slug', $blog->read_permission)->first();
		if ($perm == null) {
			$ms->addMessage('warning', "Read Permission '{$blog->read_permission}' has already been deleted.");	
		} else {
			$perm->delete();
		}
		
		$perm = Permission::where('slug', $blog->write_permission)->first();
		if ($perm == null) {
			$ms->addMessage('warning', "Write Permission '{$blog->write_permission}' has already been deleted.");	
		} else {
			$perm->delete();
		}
		$blog_slug = $blog->slug;
		$blog->delete();
		
		$ms->addMessage('success', "Successfully deleted blog '$blog_slug'.");
	}
	
	function getBlog(Request $request, Response $response, $args) {
	
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		if ($blog == null) {
			throw new NotFoundException($request, $response);	
		}
		
		return $response->withJson($blog->toArray());
	
	}
}

