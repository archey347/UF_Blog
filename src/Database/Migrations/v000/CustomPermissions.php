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

    public function up()
    {
        // Add default permissions
      $permissions = $this->permissions();

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            $roleAdmin = Role::where('slug', 'site-admin')->first();
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
                if ($roleAdmin) {
                    $roleAdmin->permissions()->syncWithoutDetaching([
                        $permission->id
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $permissions = $this->permissions();
        foreach ($permissions as $id => $permissionInfo) {
            $permission = Permission::where("slug", $permissionInfo['slug'])->first();
            $permission->delete();
        }
    }

    public function permissions()
    {
      $permissions = [
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
      return $permissions;
    }
}
