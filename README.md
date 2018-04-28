# userfrosting-blog
Blog sprinkle for Userfrosting.

## Installation

1. Clone this repo into the sprinkles folder of your Userfrosting instance. *Make sure to rename the folder to `blog` rather than userfrosting-blog*.
```bash
git clone https://github.com/archey347/userfrosting-blog.git blog
```

2. Add the sprinkle `blog` to your sprinkles.json file. It should look something like below (you may have other sprinkles already loaded).
```json
{
	"require": {
	},
	"base": [
		"core",
		"account",
		"admin",
		"blog"
	]
}
```

3.
Run Composer Update **(Not As Root)**
```
composer update
```

4. Run the bakery migration. Go to the root folder of your Userfrosting instance in a command line and run:
```bash
php bakery migrate
```
*If an error comes up about foreign keys, try re-running the command*

## Permissions

When the sprinkle is first installed, there are two permissions:

1. `uri_blog_manager`
2. `uri_blog_manager_view`

Both permissions allow access to managing the blogs, however, `uri_blog_manager_view` gives only read access to the blog managment (This is useful if you want to allow somebody to add or remove posts to the blogs but not actually manage them).

For each blog, a read and write permission is created which can be used to control who has access. There is also a 'public' option which doesn't require an authenticated session to view the blog.
