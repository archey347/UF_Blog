<?php

namespace UserFrosting\Sprinkle\Blog\Blog;

use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

class Locations implements SprinkleRecipe, TwigExtensionRecipe, MigrationRecipe, SeedRecipe
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
        ];
    }

    public function getSeeds(): array
    {
        return [
        ];
    }
}