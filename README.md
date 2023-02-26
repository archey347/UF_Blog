# userfrosting-blog
Blog sprinkle for Userfrosting.

![Screenshot of Blog and Dasboard](https://raw.githubusercontent.com/archey347/userfrosting-blog/master/Capture.PNG)

## Installation

1. Add the sprinkle `blog` to your sprinkles.json file. It should look something like below (you may have other sprinkles already loaded).
```json
{
	"require": {
		"archey347/uf_blog" : "^v0.2.1"
	},
	"base": [
		"core",
		"account",
		"admin",
		"blog"
	]
}
```

2. Run Composer Update **(Not As Root)**

```
composer update
```

4. Run the bakery migration to create the required database tables. Go to the root folder of your Userfrosting instance in a command line and run:
```bash
php bakery migrate
```
5. If you have multiple sprinkles, you may need to change the side menu twig template.

6. If you get any problem with the CKeditor, you might be needed to re-install the assets again by running this command, 
```bash
php bakery build-assets -f
```   

To do this, open `templates/navigation/sidebar-menu.html.twig` and edit the directory in the first line so that it extends the side menu template in one of the sprinkles rather than the main admin sprinkle.

## Using The Blog

The blog can be included into a webpage using an iframe, like below:

```html
<iframe src="https://{{site.uri.public}}/blogs/b/{{blog_slug}}/view"></iframe>
```

Make sure to replace `{{blog_slug}}` with the blog you want to display, or define it as a variable when you call the twig template engine on the main page.

## WYSIWYG Editor

The blog uses the CKeditor 5 to allow for basic formatting in blog posts

## Blog Formatting

To change the formatting of the blog, edit the twig template at `templates/pages/blog-view.html.twig`.

## Permissions

When the sprinkle is first installed, there are two permissions:

1. `uri_blog_manager`
2. `uri_blog_manager_view`

Both permissions allow access to managing the blogs, however, `uri_blog_manager_view` gives only read access to the blog managment (This is useful if you want to allow somebody to add or remove posts to the blogs but not actually manage them).

For each blog, a read and write permission is created which can be used to control who has access. There is also a 'public' option which doesn't require an authenticated session to view the blog.

## Contributing

Please read the [contributing guidelines](CONTRIBUTING.md).
