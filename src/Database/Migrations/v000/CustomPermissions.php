<?php

namespace UserFrosting\Sprinkle\Blog\Database\Migrations\v000;

use UserFrosting\System\Bakery\Migration;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

class CustomPermissions extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable'
    ];

    public function seed()
    {
        // Add default permissions
        $permissions = [
            'uri_blog_manager' => new Permission([
                'slug' => 'uri_blog_manager',
                'name' => 'Blog Manager',
                'conditions' => 'always()',
                'description' => 'Allows creating and managing of blogs.'
            ])
        ];

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
            }
        }
		
		// Automatically add permissions to particular roles
        $roleAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleAdmin) {
            $roleAdmin->permissions()->syncWithoutDetaching([
                $permissions['uri_members']->id,
                $permissions['uri_owls']->id
            ]);
        }
    }
}