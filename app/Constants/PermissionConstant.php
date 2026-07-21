<?php
namespace App\Constants;

class PermissionConstant
{
    /**
     * Users Module
     */
    public const USER_CREATE = 'user.create';
    public const USER_VIEW   = 'user.view';
    public const USER_UPDATE = 'user.update';
    public const USER_DELETE = 'user.delete';

    /**
     * Roles Module
     */
    public const ROLE_CREATE = 'role.create';
    public const ROLE_VIEW   = 'role.view';
    public const ROLE_UPDATE = 'role.update';
    public const ROLE_DELETE = 'role.delete';

    /**
     * Permissions Module
     */
    public const PERMISSION_CREATE = 'permission.create';
    public const PERMISSION_VIEW   = 'permission.view';
    public const PERMISSION_UPDATE = 'permission.update';
    public const PERMISSION_DELETE = 'permission.delete';

    /**
     * Rooms Module
     */
    public const ROOM_CREATE = 'room.create';
    public const ROOM_VIEW   = 'room.view';
    public const ROOM_UPDATE = 'room.update';
    public const ROOM_DELETE = 'room.delete';

    /**
     * Location Module
     */
    public const LOCATION_CREATE = 'location.create';
    public const LOCATION_VIEW   = 'location.view';
    public const LOCATION_UPDATE = 'location.update';
    public const LOCATION_DELETE = 'location.delete';


    
    /**
     * Logs Module
     */
    public const LOG_VIEW   = 'logs.view';
    public const LOG_EXPORT = 'logs.export';
    public const LOG_CREATE = 'logs.create';
    public const LOG_UPDATE = 'logs.update';
    public const LOG_DELETE = 'logs.delete';

    /**
     * Reports Module
     */
    public const REPORT_VIEW   = 'report.view';
    public const REPORT_EXPORT = 'report.export';

    /**
     * Backup Module
     */
    public const BACKUP_VIEW        = 'backup.view';
    public const BACKUP_CREATE      = 'backup.create';
    public const BACKUP_DELETE      = 'backup.delete';
    public const BACKUP_DOWNLOAD    = 'backup.download';
    public const BACKUP_FILE_DELETE = 'backup.file-delete';

    /**
     * AC Docs Module
     */
    public const ACDOCS_VIEW = 'acdocs.view';

    /**
     * Criteria Categories Module
     */
    public const CRITERIA_CATEGORY_CREATE = 'criteria-category.create';
    public const CRITERIA_CATEGORY_VIEW   = 'criteria-category.view';
    public const CRITERIA_CATEGORY_UPDATE = 'criteria-category.update';
    public const CRITERIA_CATEGORY_DELETE = 'criteria-category.delete';

    /**
     * Criteria Module
     */
    public const CRITERIA_CREATE = 'criteria.create';
    public const CRITERIA_VIEW   = 'criteria.view';
    public const CRITERIA_UPDATE = 'criteria.update';
    public const CRITERIA_DELETE = 'criteria.delete';

    /**
     * Evaluation Templates Module
     */
    public const EVALUATION_TEMPLATE_CREATE = 'evaluation-template.create';
    public const EVALUATION_TEMPLATE_VIEW   = 'evaluation-template.view';
    public const EVALUATION_TEMPLATE_UPDATE = 'evaluation-template.update';
    public const EVALUATION_TEMPLATE_DELETE = 'evaluation-template.delete';

    /**
     * Evaluations Module
     */
    public const EVALUATION_CREATE     = 'evaluation.create';
    public const EVALUATION_VIEW       = 'evaluation.view';
    public const EVALUATION_UPDATE     = 'evaluation.update';
    public const EVALUATION_UPDATE_OWN   = 'evaluation.update.own';
    public const EVALUATION_VIEW_HISTORY = 'evaluation.view-history';
    public const EVALUATION_DELETE       = 'evaluation.delete';

    /**
     * User Assignments Module
     */
    public const USER_ASSIGNMENT_CREATE = 'user-assignment.create';
    public const USER_ASSIGNMENT_VIEW   = 'user-assignment.view';
    public const USER_ASSIGNMENT_UPDATE = 'user-assignment.update';
    public const USER_ASSIGNMENT_DELETE = 'user-assignment.delete';

    /**
     * Department Module
     */
    public const DEPARTMENT_CREATE = 'department.create';
    public const DEPARTMENT_VIEW   = 'department.view';
    public const DEPARTMENT_UPDATE = 'department.update';
    public const DEPARTMENT_DELETE = 'department.delete';

    /**
     * Unit Module
     */
    public const UNIT_CREATE = 'unit.create';
    public const UNIT_VIEW   = 'unit.view';
    public const UNIT_UPDATE = 'unit.update';
    public const UNIT_DELETE = 'unit.delete';

    /**
     * Home / Dashboard
     */
    public const HOME_VIEW_KPI = 'home.view-kpi';

    /**
     * Return all permissions
     */
    public static function all(): array
    {
        return [
            // Users
            self::USER_CREATE,
            self::USER_VIEW,
            self::USER_UPDATE,
            self::USER_DELETE,

            // Roles
            self::ROLE_CREATE,
            self::ROLE_VIEW,
            self::ROLE_UPDATE,
            self::ROLE_DELETE,

            // Permissions
            self::PERMISSION_CREATE,
            self::PERMISSION_VIEW,
            self::PERMISSION_UPDATE,
            self::PERMISSION_DELETE,

            // Rooms
            self::ROOM_CREATE,
            self::ROOM_VIEW,
            self::ROOM_UPDATE,
            self::ROOM_DELETE,

            // Campus
            self::LOCATION_CREATE,
            self::LOCATION_VIEW,
            self::LOCATION_UPDATE,
            self::LOCATION_DELETE,

            // Logs
            self::LOG_VIEW,
            self::LOG_EXPORT,
            self::LOG_CREATE,
            self::LOG_UPDATE,
            self::LOG_DELETE,

            // Reports
            self::REPORT_VIEW,
            self::REPORT_EXPORT,

            // Backups
            self::BACKUP_VIEW,
            self::BACKUP_CREATE,
            self::BACKUP_DELETE,
            self::BACKUP_DOWNLOAD,
            self::BACKUP_FILE_DELETE,

            // AC Docs
            self::ACDOCS_VIEW,

            // Criteria Categories
            self::CRITERIA_CATEGORY_CREATE,
            self::CRITERIA_CATEGORY_VIEW,
            self::CRITERIA_CATEGORY_UPDATE,
            self::CRITERIA_CATEGORY_DELETE,

            // Criteria
            self::CRITERIA_CREATE,
            self::CRITERIA_VIEW,
            self::CRITERIA_UPDATE,
            self::CRITERIA_DELETE,

            // Evaluation Templates
            self::EVALUATION_TEMPLATE_CREATE,
            self::EVALUATION_TEMPLATE_VIEW,
            self::EVALUATION_TEMPLATE_UPDATE,
            self::EVALUATION_TEMPLATE_DELETE,

            // Evaluations
            self::EVALUATION_CREATE,
            self::EVALUATION_VIEW,
            self::EVALUATION_UPDATE,
            self::EVALUATION_UPDATE_OWN,
            self::EVALUATION_VIEW_HISTORY,
            self::EVALUATION_DELETE,

            // User Assignments
            self::USER_ASSIGNMENT_CREATE,
            self::USER_ASSIGNMENT_VIEW,
            self::USER_ASSIGNMENT_UPDATE,
            self::USER_ASSIGNMENT_DELETE,

            // Departments
            self::DEPARTMENT_CREATE,
            self::DEPARTMENT_VIEW,
            self::DEPARTMENT_UPDATE,
            self::DEPARTMENT_DELETE,

            // Units
            self::UNIT_CREATE,
            self::UNIT_VIEW,
            self::UNIT_UPDATE,
            self::UNIT_DELETE,

            // Home / Dashboard
            self::HOME_VIEW_KPI,
        ];
    }
}


