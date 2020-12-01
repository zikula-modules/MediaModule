<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Helper;

class PHPIniHelper
{
    public static function getMemoryLimit()
    {
        return static::phpSizeToBytes(ini_get('memory_limit'));
    }

    public static function getMaxUploadSize()
    {
        $max = static::phpSizeToBytes(ini_get('post_max_size'));
        $uploadMax = static::phpSizeToBytes(ini_get('upload_max_filesize'));
        if ($uploadMax > 0 && $uploadMax < $max) {
            $max = $uploadMax;
        }

        return $max;
    }

    /**
     * @param $size
     * @param int $precision
     *
     * @return string
     *
     * Based on http://stackoverflow.com/a/2510540/2560557
     * by John Himmelman http://stackoverflow.com/users/194676/john-himmelman
     * and Chris Jester-Young http://stackoverflow.com/users/13/chris-jester-young
     */
    public static function formatFileSize($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(1024 ** ($base - floor($base)), $precision) . $suffixes[(int) floor($base)] . 'B';
    }

    /**
     * Converts a PHP filesize like 128M to bytes.
     *
     * @param string $sSize the PHP filesize
     *
     * @return int Size in bytes
     *
     * Based on http://stackoverflow.com/a/22500394/2560557
     * by Deckard http://stackoverflow.com/users/974390/deckard
     */
    private static function phpSizeToBytes($sSize)
    {
        if (is_numeric($sSize)) {
            return $sSize;
        }
        $sSuffix = mb_substr($sSize, -1);
        $iValue = mb_substr($sSize, 0, -1);
        switch (mb_strtoupper($sSuffix)) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'P':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            // no break
            case 'T':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            // no break
            case 'G':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            // no break
            case 'M':
                $iValue *= 1024;
                // no break
            case 'K':
                $iValue *= 1024;
                break;
        }

        return $iValue;
    }
}
