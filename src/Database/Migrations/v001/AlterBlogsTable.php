<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v001;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class AlterBlogsTable extends Migration
{
    public function up()
    {
        $this->schema->table('blogs', function (Blueprint $table) {
            $table->string('blog_style_slug', 500)->unique()->comment('Style twig template file');
        });
    }

    public function down()
    {
        $this->schema->table('blogs', function (Blueprint $table) {
            $table->dropColumn('blog_style_slug');
        });
    }
}
