<?php

namespace LogicLeap\PhpServerCore;

class FileHandler
{
    public const EXTENSIONS_MIME_TYPES = [
        '.css' => 'text/css',
        '.csv' => 'text/csv',
        '.gif' => 'image/gif',
        '.html' => 'text/html',
        '.ico' => 'image/vnd.microsoft.icon',
        '.jpg' => 'image/jpeg',
        '.js' => 'text/javascript',
        '.json' => 'application/json',
        '.mp3' => 'audio/mpeg',
        '.mp4' => 'video/mp4',
        '.mpeg' => 'video/mpeg',
        '.png' => 'image/png',
        '.pdf' => 'application/pdf',
        '.php' => 'application/x-httpd-php',
        '.svg' => 'image/svg+xml',
        '.tif' => 'image/tiff',
        '.ts' => 'video/mp2t',
        '.ttf' => 'font/ttf',
        '.txt' => 'text/plain',
        '.wav' => 'audio/wav',
        '.weba' => 'audio/webm',
        '.webm' => 'video/webm',
        '.webp' => 'image/webp',
        '.xml' => 'application/xml',
        '.zip' => 'application/zip'
    ];

    /**
     * Stream a file to the current request.
     * @param string $filePath File path relative to 'storage/system/' or 'storage/user/'
     * @param bool $isSystemFile If set to true, 'storage/system/' is used as base path, otherwise base
     *      path is 'storage/user/'
     * @return bool Return true if successfully streamed, false if any errors.
     */
    public static function streamFile(string $filePath, bool $isSystemFile = false): bool
    {
        $basePath = self::getBaseFolder($isSystemFile);

        if ($filePath[0] === '/')
            $filePath = substr($filePath, 1);

        $path = $basePath . $filePath;
        $path = realpath($path);
        if (!$path)
            return false;

        $fileMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
        header('Content-Type: ' . $fileMimeType);

        readfile($path);
        return true;
    }

    public static function getFileContent(string $filePath, bool $isSystemFile = false): bool|string|null
    {
        if ($filePath[0] === '/')
            $filePath = substr($filePath, 1);

        $path = self::getBaseFolder($isSystemFile) . $filePath;
        if (!file_exists($path))
            return null;

        $file = fopen($path, "r");
        $data = fread($file, filesize($path));
        fclose($file);
        return $data;
    }

    /**
     * Handle files uploaded by POST method.
     * @param string $uploadName Name used to send file in client side.
     * @param string $newFileBasePath Location relative to the base path assigned by this class.
     * @param int $maxFileSize Max file size in bytes.
     * @param array $allowedMimeTypes Array of mime types to allow upload.
     * @param bool $isSystemFile Whether to store in 'storage/system/' or 'storage/user/'
     * @return array|string Return array as ['file_name' => $newFileName] when success. Error message when failed.
     */
    public static function handleFileUpload(string $uploadName, string $newFileBasePath, int $maxFileSize,
                                            array  $allowedMimeTypes, bool $isSystemFile = false): array|string
    {
        $tmpFilePath = $_FILES[$uploadName]['tmp_name'] ?? null;
        if ($tmpFilePath === null)
            return "No file was uploaded.";

        $tmpFileSize = filesize($tmpFilePath);
        $fileMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpFilePath);

        if ($tmpFileSize === false || $fileMimeType === false || !is_uploaded_file($tmpFilePath))
            return "Invalid file.";

        if ($tmpFileSize === 0)
            return "Empty file.";

        if ($tmpFileSize > $maxFileSize)
            return "Uploaded file is too large.";

        if (!in_array($fileMimeType, $allowedMimeTypes))
            return "Invalid file format.";

        if ($newFileBasePath[0] === '/')
            $newFileBasePath = substr($newFileBasePath, 1);
        if ($newFileBasePath[-1] === '/')
            $newFileBasePath = substr($newFileBasePath, 0, -1);

        $newFileName = bin2hex(random_bytes(20)) . uniqid() . self::getExtension($fileMimeType);

        $newFileBasePath = self::getBaseFolder($isSystemFile) . $newFileBasePath . '/';
        if (!is_dir($newFileBasePath)) {
            mkdir($newFileBasePath, 0777, true);
        }

        $newFilePath = $newFileBasePath . $newFileName;
        if (move_uploaded_file($tmpFilePath, $newFilePath))
            return ['file_name' => $newFileName];
        else
            return "Failed to move the file to new location.";
    }

    /**
     * @param string $filePath File path relative to storage/system(or user)/
     * @param bool $isSystemFile Whether to store in 'storage/system/' or 'storage/user/'
     * @return bool True if successfully deleted the file, false otherwise.
     */
    public static function deleteFile(string $filePath, bool $isSystemFile = false): bool
    {
        if ($filePath[0] === '/')
            $filePath = substr($filePath, 1);
        if ($filePath[-1] === '/')
            $filePath = substr($filePath, 0, -1);

        $filePath = self::getBaseFolder($isSystemFile) . $filePath;
        $filePath = realpath($filePath);
        if ($filePath) {
            unlink($filePath);
            return true;
        }
        return false;
    }

    public static function getBaseFolder(bool $isSystemFile = false): string
    {
        if ($isSystemFile)
            return Application::$ROOT_DIR . "/storage/system/";
        else
            return Application::$ROOT_DIR . "/storage/user/";
    }

    private static function getExtension(string $mimeType): string
    {
        return array_search($mimeType, self::EXTENSIONS_MIME_TYPES);
    }
}