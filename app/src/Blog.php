<?php

namespace UserFrosting\Sprinkle\Blog;

use UserFrosting\Sprinkle\Blog\Database\Migrations\v000\BlogPostsTable;
use UserFrosting\Sprinkle\Blog\Database\Migrations\v000\BlogsTable;
use UserFrosting\Sprinkle\Blog\Database\Models\BlogPost;
use UserFrosting\Sprinkle\Blog\Database\Seeds\BlogPermissionsSeed;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

class Blog implements SprinkleRecipe, TwigExtensionRecipe, MigrationRecipe, SeedRecipe
{
    public function getName(): string
    {
        return 'Blog';
    }

    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    public function getSprinkles(): array
    {
        return [];
    }

    public function getRoutes(): array
    {
        return [
        ];
    }

    public function getServices(): array
    {
        return [
        ];
    }

    public function getTwigExtensions(): array
    {
        return [
        ];
    }

    public function getMigrations(): array
    {
        return [
            BlogsTable::class,
            BlogPostsTable::class,
        ];
    }

    public function getSeeds(): array
    {
        return [
            BlogPermissionsSeed::class,
        ];
    }
}