<?php

namespace UserFrosting\Sprinkle\Blog\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayer;
use UserFrosting\Sprinkle\Blog\Authorise\BlogAccessControlLayerInterface;

class BlogAccessControlLayerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            BlogAccessControlLayerInterface::class => \DI\autowire(BlogAccessControlLayer::class)
        ];
    }
}