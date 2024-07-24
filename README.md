# userfrosting-blog
Blog sprinkle for Userfrosting v5.1.
![Screenshot of Blog and Dasboard](https://raw.githubusercontent.com/archey347/userfrosting-blog/master/Capture.PNG)

## Installation (UNTESTED)

1. Add the package to your `composer.json`. This can be done with:
```
composer require archey347/uf_blog "version!"
```
2. Install via NPM

3. Add to main webpack entries file


4. Run the bakery migration to create the required database tables. Go to the root folder of your Userfrosting instance in a command line and run:
```bash
php bakery migrate
```
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
