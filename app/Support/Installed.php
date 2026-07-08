<?php

namespace App\Support;

class Installed
{
    public static function exists(): bool
    {
        clearstatcache();

        return file_exists(self::persistentLockPath()) || file_exists(self::rootLockPath());
    }

    public static function create(): void
    {
        $persistentLock = self::persistentLockPath();
        $persistentDir = dirname($persistentLock);

        if (! is_dir($persistentDir) && ! mkdir($persistentDir, 0755, true) && ! is_dir($persistentDir)) {
            throw new \RuntimeException("Unable to create installed lock directory: {$persistentDir}");
        }

        if (false === file_put_contents($persistentLock, '', LOCK_EX)) {
            throw new \RuntimeException("Unable to write installed lock: {$persistentLock}");
        }

        clearstatcache(true, $persistentLock);

        if (! file_exists($persistentLock)) {
            throw new \RuntimeException("Installed lock was not created: {$persistentLock}");
        }

        self::ensureRootLock($persistentLock);
    }

    public static function remove(): void
    {
        @unlink(self::rootLockPath());
        @unlink(self::persistentLockPath());
        clearstatcache();
    }

    public static function rootLockPath(): string
    {
        return base_path('installed.lock');
    }

    public static function persistentLockPath(): string
    {
        return storage_path('runtime/installed.lock');
    }

    protected static function ensureRootLock(string $persistentLock): void
    {
        $rootLock = self::rootLockPath();

        if (file_exists($rootLock)) {
            return;
        }

        if (is_link($rootLock)) {
            @unlink($rootLock);
        }

        if (function_exists('symlink')) {
            @symlink(self::relativePath($persistentLock, dirname($rootLock)), $rootLock);
        }

        if (! file_exists($rootLock)) {
            @file_put_contents($rootLock, '');
        }
    }

    protected static function relativePath(string $path, string $from): string
    {
        $path = str_replace('\\', '/', $path);
        $from = str_replace('\\', '/', $from);

        if (! str_starts_with($path, $from.'/')) {
            return $path;
        }

        return ltrim(substr($path, strlen($from)), '/');
    }
}
