# userfrosting-blog
Blog sprinkle for Userfrosting v5.1.
![Screenshot of Blog and Dasboard](https://raw.githubusercontent.com/archey347/userfrosting-blog/master/Capture.PNG)

## Installation

1. Add the package to your `composer.json`. This can be done with:
```
composer require archey347/uf_blog
```
2. Install via NPM
```
npm install @archey347/uf_blog
```
3. Add the blog sprinkle to `sprinkles` at the top of your `webpack.config.js`
```javascript
const sprinkles = {
  // ...
  Blog: require('@archey347/uf_blog/webpack.entries')
  // ...
}
```
4.
Add the Blog sprinkle to `getSprinkles` in your main sprinkle recipe
```php
// ...
use UserFrosting\Sprinkle\Blog\Blog;
// ...
class MyApp implements
    SprinkleRecipe,
    BakeryRecipe
{
    // ...
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
            AdminLTE::class,
            Blog::class,
            // ...
        ];
    }
    // ...
}
```
5. (Re)-build assets
```
php bakery assets:build
```
6. Run the bakery migration to create the required database tables.
```bash
php bakery migrate
```
7. Seed the permissions for access control
```
php bakery seed
```
Then select `UserFrosting\Sprinkle\Blog\Database\Seeds\BlogPermissionsSeed`

Hopefully, after all that, you should be able to visit `http://<your-ip>/admin/blogs` and see the blog admin page. 

## WYSIWYG Editor

The blog uses the CKeditor 5 to allow for basic formatting in blog posts

## Blog Formatting

To change the formatting of the blog, edit the twig template at `templates/pages/blog-view.html.twig`.

## Permissions

When the sprinkle is first installed, there are two permissions:

1. `uri_blog_manager`
2. `uri_blog_manager_view`

Both permissions allow access to managing the blogs, however, `uri_blog_manager_view` gives only read access to the blog managment (This is useful if you want to allow somebody to add or remove posts to the blogs but not actually manage them).

By default, all blogs are publicly viewable.

Permissions can be customised on a per-blog basis, by overriding the blog access control layer with a class that implements the interface `UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface`.

## Contributing

Please read the [contributing guidelines](CONTRIBUTING.md).
