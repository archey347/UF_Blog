<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v000;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Databasvar;
use UserFrosting\System\Bakery\Migration;

class BlogsTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ];
    
    public function up()
    {
        if (!$this->schema->hasTable('blogs')) {
            $this->schema->create('blogs', function (Blueprint $table) {
                
                $table->increments('id');
                $table->string('slug', 255)->unique();
                $table->string('title', 255)->nullable();
                $table->string('read_permission', 255);
                $table->string('write_permission', 255);

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                
                $table->foreign('read_permission')->references('slug')->on('permissions');
                $table->index('read_permission');
 
 
                $table->foreign('write_permission')->references('slug')->on('permissions');
                $table->index('write_permission');
            });
        }
    }

    public function down()
    {
        $this->schema->drop('blogs');
    }
}
