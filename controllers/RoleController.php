<?php
require_once __DIR__ . '/../includes/url.php';

class RoleController
{
    public static function pathForRole(string $role): string
    {
        switch ($role) {
            case 'admin':
                return url_for('/admin/');
            case 'student':
                return url_for('/student/');
            case 'ccsc':
                return url_for('/ccsc/');
            default:
                return url_for('/');
        }
    }

    public static function redirectToRoleDashboard(string $role): void
    {
        $path = self::pathForRole($role);
        header('Location: ' . $path);
        exit;
    }
}