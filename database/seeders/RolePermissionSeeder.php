<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions a platform admin gets. Everything except the Shield role
     * management bits, which stay exclusive to super_admin.
     */
    private const ADMIN_EXCLUDED = [
        'view_any_role',
        'view_role',
        'create_role',
        'update_role',
        'delete_role',
        'delete_any_role',
    ];

    private const INSTITUTE_ADMIN_EXCLUDED = [
        'view_any_role',
        'view_role',
        'create_role',
        'update_role',
        'delete_role',
        'delete_any_role',
        'create_hero',
        'view_any_hero',
        'update_hero',
        'delete_hero',
        'delete_any_hero',
        'create_institute',
        'delete_institute',
        'delete_any_institute',
        'delete_any_user',
        'force_delete_any_user',
    ];

    private const TEACHER_ALLOWED = [
        'view_any_subject', 'view_subject',
        'view_any_topic', 'view_topic', 'create_topic', 'update_topic',
        'view_any_question', 'view_question', 'create_question', 'update_question', 'delete_question',
        'view_any_examination', 'view_examination', 'create_examination', 'update_examination',
        'view_any_exam::attempt', 'view_exam::attempt',
        'view_any_result', 'view_result',
        'view_any_study::material', 'view_study::material',
        'view_any_category', 'view_category',
        'view_any_p::d::f::extraction', 'view_p::d::f::extraction', 'create_p::d::f::extraction',
        'view_any_a::i::exam::generation', 'view_a::i::exam::generation', 'create_a::i::exam::generation',
        'view_any_o::m::r::sheet', 'view_o::m::r::sheet',
        'view_any_user', 'view_user',
    ];

    private const STUDENT_ALLOWED = [
        'view_any_examination', 'view_examination',
        'view_any_exam::attempt', 'view_exam::attempt',
        'view_any_result', 'view_result',
        'view_any_category', 'view_category',
        'view_any_study::material', 'view_study::material',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = Permission::pluck('name')->all();

        $this->sync('admin', $all, self::ADMIN_EXCLUDED);
        $this->sync('institute_admin', $all, self::INSTITUTE_ADMIN_EXCLUDED);
        $this->sync('teacher', $all, [], self::TEACHER_ALLOWED);
        $this->sync('student', $all, [], self::STUDENT_ALLOWED);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $all
     * @param  array<int, string>  $excluded
     * @param  array<int, string>|null  $allowList
     */
    private function sync(string $roleName, array $all, array $excluded, ?array $allowList = null): void
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        $names = $allowList ?? array_values(array_diff($all, $excluded));

        $permissions = Permission::whereIn('name', $names)->get();

        $role->syncPermissions($permissions);
    }
}
