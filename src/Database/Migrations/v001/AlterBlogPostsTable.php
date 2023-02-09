<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v001;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class AlterBlogPostsTable extends Migration
{
    public function up()
    {
        $this->schema->table('blog_posts', function (Blueprint $table) {
            $table->string('blog_post_style_slug');
            $table->string('blog_og_image', 500);
            $table->string('blog_twitter_card_image', 500);
        });
    }

    public function down()
    {
        $this->schema->table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('blog_twitter_card_image');
            $table->dropColumn('blog_og_image');
            $table->dropColumn('blog_post_style_slug');
        });
    }
}
