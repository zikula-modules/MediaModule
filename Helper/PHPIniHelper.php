<?php


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

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[(int)floor($base)] . 'B';
    }

    /**
     * Converts a PHP filesize like 128M to bytes.
     *
     * @param string $sSize The PHP filesize.
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
        $sSuffix = substr($sSize, -1);
        $iValue = substr($sSize, 0, -1);
        switch (strtoupper($sSuffix)) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'P':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'T':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'G':
                $iValue *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'M':
                $iValue *= 1024;
            case 'K':
                $iValue *= 1024;
                break;
        }

        return $iValue;
    }
}
