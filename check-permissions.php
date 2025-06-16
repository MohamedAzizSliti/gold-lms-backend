<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🔍 Checking User Permissions for Attachment Upload\n\n";

// Check all users with teacher role
$teachers = User::whereHas('roles', function($query) {
    $query->where('name', 'teacher');
})->get();

echo "👨‍🏫 Teachers found: " . $teachers->count() . "\n";

foreach ($teachers as $teacher) {
    echo "\n📋 Teacher: {$teacher->name} ({$teacher->email})\n";
    echo "   Role: " . $teacher->roles->pluck('name')->implode(', ') . "\n";
    
    // Check specific permissions
    $permissions = [
        'attachment.create',
        'attachment.index',
        'attachment.destroy',
        'course.create',
        'course.edit'
    ];
    
    foreach ($permissions as $permission) {
        $hasPermission = $teacher->can($permission);
        $status = $hasPermission ? '✅' : '❌';
        echo "   {$status} {$permission}\n";
    }
}

// Check all roles and their permissions
echo "\n🎭 All Roles and Permissions:\n";
$roles = Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "\n📝 Role: {$role->name}\n";
    $attachmentPermissions = $role->permissions->filter(function($permission) {
        return str_contains($permission->name, 'attachment') || 
               str_contains($permission->name, 'course') ||
               str_contains($permission->name, 'chapter') ||
               str_contains($permission->name, 'quiz') ||
               str_contains($permission->name, 'exam');
    });
    
    if ($attachmentPermissions->count() > 0) {
        foreach ($attachmentPermissions as $permission) {
            echo "   ✅ {$permission->name}\n";
        }
    } else {
        echo "   ❌ No relevant permissions\n";
    }
}

// Check if attachment.create permission exists
echo "\n🔐 Permission Check:\n";
$attachmentCreatePermission = Permission::where('name', 'attachment.create')->first();
if ($attachmentCreatePermission) {
    echo "✅ attachment.create permission exists (ID: {$attachmentCreatePermission->id})\n";
    
    $rolesWithPermission = $attachmentCreatePermission->roles;
    echo "   Assigned to roles: " . $rolesWithPermission->pluck('name')->implode(', ') . "\n";
} else {
    echo "❌ attachment.create permission does not exist\n";
}

echo "\n🎯 Recommendation:\n";
echo "If teachers still can't upload attachments, check:\n";
echo "1. User is properly authenticated\n";
echo "2. User has teacher role assigned\n";
echo "3. Teacher role has attachment.create permission\n";
echo "4. Clear permission cache: php artisan permission:cache-reset\n";

?>
