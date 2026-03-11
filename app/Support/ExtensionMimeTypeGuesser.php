<?php

namespace App\Support;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * 當 fileinfo 擴展不可用時，基於副檔名與檔案魔術字節猜測 MIME 類型
 * 避免 getExtensions(null) 導致 TypeError，並支援臨時上傳檔（.tmp）的圖片辨識
 */
class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private const EXTENSION_MAP = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'heic' => 'image/heic',
        'avif' => 'image/avif',
    ];

    public function isGuesserSupported(): bool
    {
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (isset(self::EXTENSION_MAP[$ext])) {
            return self::EXTENSION_MAP[$ext];
        }

        // 臨時檔或未知副檔名：嘗試從檔案內容辨識
        if (is_file($path) && is_readable($path)) {
            $mime = $this->guessFromMagicBytes($path);
            if ($mime !== null) {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    private function guessFromMagicBytes(string $path): ?string
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return null;
        }

        $bytes = fread($handle, 12);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 3) {
            return null;
        }

        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }
        if (str_starts_with($bytes, "\x89PNG\r\n\x1A\n")) {
            return 'image/png';
        }
        if (str_starts_with($bytes, "GIF87a") || str_starts_with($bytes, "GIF89a")) {
            return 'image/gif';
        }
        if (str_starts_with($bytes, "RIFF") && strlen($bytes) >= 12 && substr($bytes, 8, 4) === "WEBP") {
            return 'image/webp';
        }
        if (str_starts_with($bytes, "BM")) {
            return 'image/bmp';
        }

        return null;
    }
}
