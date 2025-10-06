<?php
function app_base_path(): string
{
    // Determine app root segment for URLs.
    // Examples:
    //  - Built-in server: SCRIPT_NAME "/index.php" or "/student/index.php" => base "/"
    //  - XAMPP: SCRIPT_NAME "/ccsmf/index.php" or "/ccsmf/student/index.php" => base "/ccsmf"
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $script = str_replace('\\', '/', $script);
    $parts = explode('/', trim($script, '/'));
    if (count($parts) <= 1) {
        return '/';
    }
    $first = $parts[0];
    // If first segment is one of the role folders, treat app base as "/"
    if (in_array($first, ['student', 'admin', 'ccsc'], true)) {
        return '/';
    }
    return '/' . $first;
}

function url_for(string $path): string
{
    $base = app_base_path();
    $path = '/' . ltrim($path, '/');
    return rtrim($base, '/') . $path;
}