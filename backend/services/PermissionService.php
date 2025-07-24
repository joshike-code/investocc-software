<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';

class PermissionService
{
    public static function getAllPermissions(): array {
        return [
            'view_users',
            'manage_users',
            'view_orders',
            'manage_orders',
            'view_payments',
            'view_order_stats',
            'view_referral_earnings',
            'manage_payment_wallets',
            'manage_platform_settings',
            'manage_crypto',
            'manage_admins',
            'manage_bank_accounts',
            'manage_bank_payments',
            'manage_withdrawals',
            'manage_plans',
        ];
    }

    public static function isValidPermission(string $permission): bool {
        return in_array($permission, self::getAllPermissions());
    }

    public static function validatePermissions(array $inputPermissions): bool {
        foreach ($inputPermissions as $permission) {
            if (!self::isValidPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public static function hasPermission($user, $permission)
    {
        $user_role = $user->role;
        $user_permission = $user->permissions;
        if ($user_role === 'superadmin') {
            return true; // superadmin has all permissions
        }

        if (!isset($user_permission)) {
            return false;
        }

        $permissions = json_decode($user_permission, true);
        return in_array($permission, $permissions);
    }
}



?>