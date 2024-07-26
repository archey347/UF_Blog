<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v001;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class AlterBlogPostsTable extends Migration
{
    public function up()
    {
        $this->schema->table('blog_posts', function (Blueprint $table) {
            $table->string('post_style_slug');
            $table->string('post_og_image', 500);
        });
    }

    public function down()
    {
        $this->schema->table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('post_og_image');
            $table->dropColumn('post_style_slug');
        });
    }
}
