<?php

namespace App\Support;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * 當 fileinfo 擴展不可用時，基於副檔名猜測 MIME 類型
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
    ];

    public function isGuesserSupported(): bool
    {
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return self::EXTENSION_MAP[$ext] ?? null;
    }
}
