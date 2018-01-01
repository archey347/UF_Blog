<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v000;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;

class BlogPostsTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Blog\Database\Migrations\v000\BlogsTable'
    ];
    
    public function up()
    {
        if (!$this->schema->hasTable('blog_posts')) {
            $this->schema->create('blog_posts', function (Blueprint $table) {
                
                $table->increments('id');
                $table->integer('blog_id')->unsigned();
                
                $table->string('title', 255);
                $table->text('content');
                
                $table->integer('last_updates_by')->unsigned();
                $table->integer('author')->unsigned();
                
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                
                $table->foreign('last_updates_by')->references('id')->on('users');
                $table->index('last_updates_by');
                $table->foreign('author')->references('id')->on('users');
                $table->index('author');
            });
        }
    }

    public function down()
    {
        $this->schema->drop('blog_posts');
    }
}