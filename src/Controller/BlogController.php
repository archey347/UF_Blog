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
use UserFrosting\Sprinkle\Blog\Sprunje\PostSprunje;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Sprinkle\Blog\Database\Models\Blog;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;

class BlogController extends SimpleController
{
    public function displayBlogAdmin(Request $request, Response $response, $args)
    {
		/** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if ((!$authorizer->checkAccess($currentUser, 'uri_blog_manager_view')) and (!$authorizer->checkAccess($currentUser, 'uri_blog_manager'))) {
            throw new ForbiddenException();
        }
		
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
        if ((!$authorizer->checkAccess($currentUser, 'uri_blog_manager_view')) and (!$authorizer->checkAccess($currentUser, 'uri_blog_manager'))) {
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
		$this->checkAccess('uri_blog_manager');
        return $this->ci->view->render($response, 'modals/blog.html.twig',
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
	
	public function createBlog(Request $request, Response $response, $args) {
		$this->checkAccess('uri_blog_manager');
		
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
		$createReadPermission = false;
		if($data['public'] == 0) {
			// Check that the permissions don't already exist
			$perms = Permission::where('slug', $data['read_permission'])->get();
			if ($perms->count()) {
				$ms->addMessage('danger', "Read Permission '".$data['read_permission']."' already exists.");
				return $response->withStatus(400);	
			} else {
				// If it doesn't exist, then create it
				$createReadPermission = true;
			}
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
		
		if($createReadPermission) {
			$perm = new Permission;
			$perm->slug = $data['read_permission'];
			$perm->name = "View Blog '".$data['blog_slug']."'";
			$perm->conditions = "always()";
			$perm->description = "Gives read access to the '".$data['blog_slug']."' blog.";
			$perm->save();
		}
		
		// Create Blog
		$blog = new Blog;
		
		$blog->title = $data['blog_name'];
		$blog->slug = $data['blog_slug'];
		$blog->read_permission = $data['public'] == 1 ? "" : $data['read_permission'];
		$blog->write_permission = $data['write_permission'];
		$blog->public = $data['public'] == 1;
		$blog->save();
		
		$ms->addMessage('success', "Successfully added blog '".$data['blog_slug']."'.");
		
	}
	
	public function getModalEdit(Request $request, Response $response, $args) {
		$this->checkAccess('uri_blog_manager');
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
					"action" => "api/blogs/b/$blog_slug",
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
					"public" => $blog->public,
					"read_id" => Permission::where('slug', $blog->read_permission)->first()->id,
					"write_id" => Permission::where('slug', $blog->write_permission)->first()->id
				]
            ]
        );    
    }
	
	public function updateBlog(Request $request, Response $response, $args) {
		$this->checkAccess('uri_blog_manager');
		// Get submitted data
		$params = $request->getParsedBody();
		/** @var UserFrosting\Sprinkle\Core\MessageStream $ms */
		$ms = $this->ci->alerts;
		
		if(!isset($args['blog_slug'])) {
			$ms->addMessage('danger', "No Blog set.");
			return $response->withStatus(400);		
		}
		
		// Load the request schema
		$schema = new RequestSchema('schema://requests/edit-blog.yaml');
		
		// Whitelist and set parameter defaults
		$transformer = new RequestDataTransformer($schema);
		$data = $transformer->transform($params);
		
		
		
		$validator = new ServerSideValidator($schema, $this->ci->translator);
		
		// Add error messages and halt if validation failed
		if (!$validator->validate($data)) {
			$ms->addValidationErrors($validator);
			return $response->withStatus(400);
		}
		
		// Check to make sure that the sent id is valid
		$current_blog = Blog::where('slug', $args['blog_slug'])->first();
		if ($current_blog == null) {
			$ms->addMessage('danger', "Blog not found.");
			return $response->withStatus(400);	
		}
		
		// Check if that blog name already exists and is not the blog currently being modified
		$blog = Blog::where('slug', $datas['blog_slug'])->first();
		if ($blog != null && $blog->id != $current_blog->id) {
			$ms->addMessage('danger', "Blog '".$data['blog_slug']."' already exists.");
			return $response->withStatus(400);	
		}
		$update_read_perm = false;
		$delete_read_perm = false;
		$create_read_perm = false;
		if($current_blog['public'] == false && $data['public'] == 1) {
			$delete_read_perm = true;
		}
		// Check that the permissions don't already exist
		$read_perm = Permission::where('slug', $data['read_permission'])->first();
		if ($read_perm != null && $read_perm->id != $data['read_id']) {
			$ms->addMessage('danger', "Read Permission '".$data['read_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			if($read_perm == null) {
				$read_perm = Permission::find($data['read_id']);	
			}
			if($read_perm == null) {
				$create_read_perm = true;
			} else {
				$update_read_perm = true;	
			}
			
		}
		
		// Check that the permissions don't already exist
		$write_perm = Permission::where('slug', $data['write_permission'])->first();
		if ($write_perm != null && $perm->id != $data['write_id']) {
			$ms->addMessage('danger', "Write Permission '".$data['write_permission']."' already exists.");
			return $response->withStatus(400);	
		} else {
			if($write_perm == null) {
				$write_perm = Permission::find($data['write_id']);
			}
			$write_perm->slug = $data['write_permission'];
			$write_perm->name = "Edit Blog '".$data['blog_slug']."'";
			$write_perm->conditions = "always()";
			$write_perm->description = "Gives write access to the '".$data['blog_slug']."' blog.";
			$write_perm->save();	
		}
		if($create_read_perm) {
			$read_perm = new Permission;
			$read_perm->slug = $data['read_permission'];
			$read_perm->name = "View Blog '".$data['blog_slug']."'";
			$read_perm->conditions = "always()";
			$read_perm->description = "Gives read access to the '".$data['blog_slug']."' blog.";
			$read_perm->save();	
		}
		if($update_read_perm) {
			$read_perm->slug = $data['read_permission'];
			$read_perm->name = "View Blog '".$data['blog_slug']."'";
			$read_perm->conditions = "always()";
			$read_perm->description = "Gives read access to the '".$data['blog_slug']."' blog.";
			$read_perm->save();
		}
		if($delete_read_perm) {
			$read_perm->delete();
		}
		
		
		$current_blog->title = $data['blog_name'];
		$current_blog->slug = $data['blog_slug'];
		
		$current_blog->read_permission = $data['public'] == 1 ? "" : $data['read_permission'];
		$current_blog->write_permission = $data['write_permission'];
		
		$current_blog->public = $data['public'];
		
		$current_blog->save();
		
		$ms->addMessage('success', "Successfully updated blog '".$data['blog_slug']."'.");
		
	}
    
	public function getModalConfirmDelete(Request $request, Response $response, $args) {
		$this->checkAccess('uri_blog_manager');
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
					"action" => "api/blogs/b/$blog_slug",
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
		$this->checkAccess('uri_blog_manager');
		// Get submitted data
		$params = $request->getParsedBody();
	
		$ms = $this->ci->alerts;
		
		
		if($args['blog_slug'] == null) {
			$ms->addMessage('danger', "No Blog set.");
			return $response->withStatus(400);		
		}
		
		// Check if that blog name already exists
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		if ($blog == null) {
			$ms->addMessage('danger', "Blog selected to delete doesn't exist.");
			return $response->withStatus(400);	
		}
		
		// Check that the permission exists
		if(!$blog->public) {
			$perm = Permission::where('slug', $blog->read_permission)->first();
			if ($perm == null) {
				$ms->addMessage('warning', "Read Permission '{$blog->read_permission}' has already been deleted.");	
			} else {
				$perm->delete();
			}
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
		$this->checkAccess($blog->read_permission);
		if ($blog == null) {
			throw new NotFoundException($request, $response);	
		}
		
		return $response->withJson($blog->toArray());
	
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
	
	function genBlog(Request $request, Response $response, $args) {
		$blog_slug = $args['blog_slug'];
		
		$blog = Blog::where('slug', $args['blog_slug'])->first();
		
		if(!$blog->public) {
			$this->checkAccess($blog->read_permission);
		}
		
		$data = [
					"blog" => $blog->toArray(),
					"posts" => []
		];
		
		$posts = BlogPost::where('blog_id', $blog->id)->orderBy('created_at', 'desc')->get();
		
		foreach($posts as $post) {
			array_push($data['posts'], $post->toArray());
		}
		
		
		
		return $this->ci->view->render($response, 'pages/blog-view.html.twig', $data);   
	}
	function checkAccess($perm_slug) {
		$authorizer = $this->ci->authorizer;

		$currentUser = $this->ci->currentUser;
		
		if($perm_slug != "") {
			if (!$authorizer->checkAccess($currentUser, $perm_slug)) {
				throw new ForbiddenException();
			}
		}
	}
}

