<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckPermissions extends Command
{
    protected $signature = 'check:permissions';
    protected $description = 'Check user permissions for attachment upload';

    public function handle()
    {
        $this->info('🔍 Checking User Permissions for Attachment Upload');
        $this->newLine();

        // Check all users with teacher role
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'teacher');
        })->get();

        $this->info("👨‍🏫 Teachers found: " . $teachers->count());

        foreach ($teachers as $teacher) {
            $this->newLine();
            $this->info("📋 Teacher: {$teacher->name} ({$teacher->email})");
            $this->info("   Role: " . $teacher->roles->pluck('name')->implode(', '));
            
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
                $this->line("   {$status} {$permission}");
            }
        }

        // Check all roles and their permissions
        $this->newLine();
        $this->info('🎭 All Roles and Permissions:');
        $roles = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $this->newLine();
            $this->info("📝 Role: {$role->name}");
            $attachmentPermissions = $role->permissions->filter(function($permission) {
                return str_contains($permission->name, 'attachment') || 
                       str_contains($permission->name, 'course') ||
                       str_contains($permission->name, 'chapter') ||
                       str_contains($permission->name, 'quiz') ||
                       str_contains($permission->name, 'exam');
            });
            
            if ($attachmentPermissions->count() > 0) {
                foreach ($attachmentPermissions as $permission) {
                    $this->line("   ✅ {$permission->name}");
                }
            } else {
                $this->line("   ❌ No relevant permissions");
            }
        }

        // Check if attachment.create permission exists
        $this->newLine();
        $this->info('🔐 Permission Check:');
        $attachmentCreatePermission = Permission::where('name', 'attachment.create')->first();
        if ($attachmentCreatePermission) {
            $this->info("✅ attachment.create permission exists (ID: {$attachmentCreatePermission->id})");
            
            $rolesWithPermission = $attachmentCreatePermission->roles;
            $this->info("   Assigned to roles: " . $rolesWithPermission->pluck('name')->implode(', '));
        } else {
            $this->error("❌ attachment.create permission does not exist");
        }

        $this->newLine();
        $this->info('🎯 Recommendation:');
        $this->info('If teachers still can\'t upload attachments, check:');
        $this->info('1. User is properly authenticated');
        $this->info('2. User has teacher role assigned');
        $this->info('3. Teacher role has attachment.create permission');
        $this->info('4. Clear permission cache: php artisan permission:cache-reset');

        return 0;
    }
}
