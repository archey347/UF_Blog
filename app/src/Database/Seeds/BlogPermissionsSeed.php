<?php

namespace UserFrosting\Sprinkle\Blog\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class BlogPermissionsSeed implements SeedInterface
{
    public function run(): void {
        // Add default permissions
        $permissions = $this->permissions();

        $roles = ['site-admin'];

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;

            $permission->save();

            foreach ($roles as $role) {
                $role = Role::where('slug', $role)->first();
                if ($role) {
                    $role->permissions()->syncWithoutDetaching([
                        $permission->id
                    ]);
                }
            }
        }
    }

    public function permissions(): array
    {
      return [
        'uri_blog_manager' => new Permission([
              'slug' => 'uri_blog_manager',
              'name' => 'Blog Manager',
              'conditions' => 'always()',
              'description' => 'Allows creating and managing of blogs.'
        ]),
        'uri_blog_manager_view' => new Permission([
              'slug' => 'uri_blog_manager_view',
              'name' => 'View Blog Manager',
              'conditions' => 'always()',
              'description' => 'Allows access to view the blogs in settings.'
        ])
      ];
    }
}