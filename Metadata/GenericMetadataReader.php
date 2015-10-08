<?php

namespace Cmfcmf\Module\MediaModule\Metadata;

class GenericMetadataReader
{
    public static function readMetadata($file)
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        $getID3 = new \getID3();
        $meta = $getID3->analyze($file);
        \getid3_lib::CopyTagsToComments($meta);

        return $meta;
    }
}
