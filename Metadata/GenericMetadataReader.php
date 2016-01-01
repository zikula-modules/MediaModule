<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Metadata;

/**
 * Reads metadata from a given file.
 */
class GenericMetadataReader
{
    /**
     * Reads metadata from the given file.
     *
     * @param string $file
     *
     * @return array The metadata.
     */
    public static function readMetadata($file)
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        $getID3 = new \getID3();
        $meta = $getID3->analyze($file);
        \getid3_lib::CopyTagsToComments($meta);

        return $meta;
    }
}
