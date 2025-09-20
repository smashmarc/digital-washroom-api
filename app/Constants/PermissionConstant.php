<?php
namespace App\Constants;

class PermissionConstant
{
    /**
     * Users Module
     */
    public const USER_CREATE = 'users.create';
    public const USER_VIEW   = 'users.view';
    public const USER_UPDATE = 'users.update';
    public const USER_DELETE = 'users.delete';

    /**
     * Roles Module
     */
    public const ROLE_CREATE = 'roles.create';
    public const ROLE_VIEW   = 'roles.view';
    public const ROLE_UPDATE = 'roles.update';
    public const ROLE_DELETE = 'roles.delete';

    /**
     * Permissions Module
     */
    public const PERMISSION_CREATE = 'permissions.create';
    public const PERMISSION_VIEW   = 'permissions.view';
    public const PERMISSION_UPDATE = 'permissions.update';
    public const PERMISSION_DELETE = 'permissions.delete';

    /**
     * Rooms Module
     */
    public const ROOM_CREATE = 'rooms.create';
    public const ROOM_VIEW   = 'rooms.view';
    public const ROOM_UPDATE = 'rooms.update';
    public const ROOM_DELETE = 'rooms.delete';

    /**
     * Campus Module
     */
    public const CAMPUS_CREATE = 'campus.create';
    public const CAMPUS_VIEW   = 'campus.view';
    public const CAMPUS_UPDATE = 'campus.update';
    public const CAMPUS_DELETE = 'campus.delete';

    /**
     * Logs Module
     */
    public const LOG_VIEW   = 'logs.view';
    public const LOG_EXPORT = 'logs.export'; // non-CRUD example

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
            self::CAMPUS_CREATE,
            self::CAMPUS_VIEW,
            self::CAMPUS_UPDATE,
            self::CAMPUS_DELETE,

            // Logs
            self::LOG_VIEW,
            self::LOG_EXPORT,
        ];
    }
}


