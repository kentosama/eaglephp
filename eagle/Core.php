<?php

namespace Eagle;

class Core
{
    public static function redirect(array $url): void
    {
        $request = new Request;
        $args = [
            'prefix' => $request->getParams('prefix'),
            'controller' => $request->getParams('controller'),
            'action' => $request->getParams('action'),
        ];

        $url = array_merge($args, $url);
        if (empty($url['action'])) {
            $url['action'] = 'index';
        }

        $queryString = http_build_query(array_filter($url));
        $url = Router::reverse('/' . $queryString);
        
        header('Location: ' . $url);
        exit();
    }

    public static function passwordHash($password): string
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function passwordVerify($password, $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function getConfig(): array
    {
        $configFile = APP_CONFIG . DS . 'app.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException("Configuration file not found: {$configFile}");
        }
        return include $configFile;
    }

    public static function slugify(string $str): string
    {
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $str = preg_replace('~[^\pL\d]+~u', '-', $str);
        $str = preg_replace('~[^-\w]+~', '', $str);
        $str = trim($str, '-');
        $str = strtolower($str);
        return $str;
    }

    public static function toBytes(string $value): ?int
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($value, 0, -2);
        $suffix = strtoupper(substr($value, -2));

        if (!in_array($suffix, $units)) {
            return null;
        }

        $exponent = array_flip($units)[$suffix];
        return $number * (1024 ** $exponent);
    }
}
