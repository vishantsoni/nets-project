<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = \App\Models\User::where('email', 'admin@nets.com')->first();
echo "User role column: " . $admin->role . "\n";
echo "User is active: " . ($admin->is_active ? 'true' : 'false') . "\n";
echo "User has super_admin role: " . ($admin->hasRole('super_admin') ? 'true' : 'false') . "\n";
echo "User roles: " . $admin->roles->pluck('name')->implode(', ') . "\n";
$superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();
if ($superAdminRole) {
    echo "Super admin role permissions count: " . $superAdminRole->permissions->count() . "\n";
    echo "Super admin role permissions: " . $superAdminRole->permissions->pluck('name')->implode(', ') . "\n";
} else {
    echo "Super admin role not found\n";
}