<?php
require_once __DIR__ . '/../services/PermissionService.php';
require_once __DIR__."/../core/jwt_utils.php";
require_once __DIR__."/../core/response.php";


class AuthMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        if(!isset($headers['Authorization'])) {
            Response::error('Authorization header missing', 410);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = validate_jwt($token);
        if(!$decoded) {
            Response::error('Invalid or expired token', 401);
        }
        return $decoded;
    }

    public static function handle($roles) {
        $user = self::authenticate();
        $user_role = $user->role;
        if (!in_array($user_role, $roles)) {
            Response::error('Access denied', 403);
        }
        return $user;
    }

    public static function requirePermission($permission) {
        $user = self::handle(['admin', 'superadmin']);

        if (!PermissionService::hasPermission($user, $permission)) {
            Response::error('Permission denied', 403);
        }
        return $user;
    }
}
?>