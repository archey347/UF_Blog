<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v000;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration;

class BlogPostsTable extends Migration
{
    public static $dependencies = [
        BlogsTable::class
    ];
    
    public function up(): void
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

                $table->foreign('blog_id')->references('id')->on('blogs');

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8'; 
            });
        }
    }

    public function down(): void
    {
        $this->schema->drop('blog_posts');
    }
}