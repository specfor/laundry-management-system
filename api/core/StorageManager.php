<?php

namespace LogicLeap\PhpServerCore;

class StorageManager
{
    /**
     * Stream a file to the current request.
     * @param string $filePath File path relative to 'storage/admin/' or 'storage/user/'
     * @param bool $isAdminFile If set to true, 'storage/admin/' is used as base path, otherwise base
     *      path is 'storage/user/'
     * @return bool Return true if successfully streamed, false if any errors.
     */
    public static function streamFile(string $filePath, bool $isAdminFile = false): bool
    {
        if ($isAdminFile)
            $basePath = Application::$ROOT_DIR . "/storage/admin/";
        else
            $basePath = Application::$ROOT_DIR . "/storage/user/";

        if ($filePath[0] === '/')
            $filePath = substr($filePath, 1);

        $path = $basePath . $filePath;
        $path = realpath($path);
        if (!$path)
            return false;

        readfile($path);
        return true;
    }
}