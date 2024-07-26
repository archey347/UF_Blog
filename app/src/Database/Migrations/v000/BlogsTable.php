<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v000;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Core\Database\Migration;

class BlogsTable extends Migration
{
    public static $dependencies = [
        PermissionsTable::class
    ];

    public function up(): void
    {
        if (!$this->schema->hasTable('blogs')) {
            $this->schema->create('blogs', function (Blueprint $table) {

                $table->increments('id');
                $table->string('title', 255)->nullable();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';

                $table->index('read_permission');
                $table->index('write_permission');
            });
        }
    }

    public function down(): void
    {
        $this->schema->drop('blogs');
    }
}
