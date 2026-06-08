<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Constants\PermissionConstant;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    private array $descriptions = [
        // Users
        PermissionConstant::USER_CREATE => 'Create new user accounts',
        PermissionConstant::USER_VIEW   => 'View user profiles and the user list',
        PermissionConstant::USER_UPDATE => 'Edit and update user details',
        PermissionConstant::USER_DELETE => 'Delete user accounts',

        // Roles
        PermissionConstant::ROLE_CREATE => 'Create new roles',
        PermissionConstant::ROLE_VIEW   => 'View roles and their assigned permissions',
        PermissionConstant::ROLE_UPDATE => 'Edit role details and manage assigned permissions',
        PermissionConstant::ROLE_DELETE => 'Delete roles',

        // Permissions
        PermissionConstant::PERMISSION_CREATE => 'Create new permissions',
        PermissionConstant::PERMISSION_VIEW   => 'View all system permissions',
        PermissionConstant::PERMISSION_UPDATE => 'Edit permission details',
        PermissionConstant::PERMISSION_DELETE => 'Delete permissions',

        // Rooms
        PermissionConstant::ROOM_CREATE => 'Add new rooms',
        PermissionConstant::ROOM_VIEW   => 'View rooms and their details',
        PermissionConstant::ROOM_UPDATE => 'Edit room information',
        PermissionConstant::ROOM_DELETE => 'Delete rooms',

        // Locations
        PermissionConstant::LOCATION_CREATE => 'Add new locations',
        PermissionConstant::LOCATION_VIEW   => 'View locations',
        PermissionConstant::LOCATION_UPDATE => 'Edit location details',
        PermissionConstant::LOCATION_DELETE => 'Delete locations',

        // Logs
        PermissionConstant::LOG_VIEW   => 'View activity and cleaning logs',
        PermissionConstant::LOG_EXPORT => 'Export logs to a file',
        PermissionConstant::LOG_CREATE => 'Create log entries',
        PermissionConstant::LOG_UPDATE => 'Edit log entries',
        PermissionConstant::LOG_DELETE => 'Delete log entries',

        // Reports
        PermissionConstant::REPORT_VIEW   => 'View reports and analytics',
        PermissionConstant::REPORT_EXPORT => 'Export reports to a file',

        // Backups
        PermissionConstant::BACKUP_VIEW        => 'View available backups',
        PermissionConstant::BACKUP_CREATE      => 'Create new database backups',
        PermissionConstant::BACKUP_DELETE      => 'Delete backups',
        PermissionConstant::BACKUP_DOWNLOAD    => 'Download backup files',
        PermissionConstant::BACKUP_FILE_DELETE => 'Delete individual backup files',

        // AC Docs
        PermissionConstant::ACDOCS_VIEW => 'View Access Control documentation',

        // Criteria Categories
        PermissionConstant::CRITERIA_CATEGORY_CREATE => 'Create new criteria categories',
        PermissionConstant::CRITERIA_CATEGORY_VIEW   => 'View criteria categories',
        PermissionConstant::CRITERIA_CATEGORY_UPDATE => 'Edit criteria categories',
        PermissionConstant::CRITERIA_CATEGORY_DELETE => 'Delete criteria categories',

        // Criteria
        PermissionConstant::CRITERIA_CREATE => 'Create new evaluation criteria',
        PermissionConstant::CRITERIA_VIEW   => 'View evaluation criteria',
        PermissionConstant::CRITERIA_UPDATE => 'Edit evaluation criteria',
        PermissionConstant::CRITERIA_DELETE => 'Delete evaluation criteria',

        // Evaluation Templates
        PermissionConstant::EVALUATION_TEMPLATE_CREATE => 'Create new evaluation templates',
        PermissionConstant::EVALUATION_TEMPLATE_VIEW   => 'View evaluation templates',
        PermissionConstant::EVALUATION_TEMPLATE_UPDATE => 'Edit evaluation templates',
        PermissionConstant::EVALUATION_TEMPLATE_DELETE => 'Delete evaluation templates',

        // Evaluations
        PermissionConstant::EVALUATION_CREATE => 'Start and create new evaluations',
        PermissionConstant::EVALUATION_VIEW   => 'View evaluations and their results',
        PermissionConstant::EVALUATION_UPDATE => 'Edit and conduct evaluations',
        PermissionConstant::EVALUATION_DELETE => 'Delete evaluations',

        // User Assignments
        PermissionConstant::USER_ASSIGNMENT_CREATE => 'Assign evaluations to users',
        PermissionConstant::USER_ASSIGNMENT_VIEW   => 'View user evaluation assignments',
        PermissionConstant::USER_ASSIGNMENT_UPDATE => 'Edit user assignment details',
        PermissionConstant::USER_ASSIGNMENT_DELETE => 'Remove user evaluation assignments',
    ];

    public function run(): void
    {
        foreach (PermissionConstant::all() as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['description' => $this->descriptions[$permission] ?? ucfirst(str_replace(['.', '-'], ' ', $permission))],
            );
        }
    }
}
