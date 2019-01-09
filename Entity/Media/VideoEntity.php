<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Cmfcmf\Module\MediaModule\Metadata\GenericMetadataReader;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class VideoEntity extends AbstractFileEntity
{
    public function onNewFile(array $info)
    {
        parent::onNewFile($info);

        $meta = GenericMetadataReader::readMetadata($info['filePath']);
        $this->extraData = $this->getInformationToKeep($meta, $meta['mime_type']);

        if (isset($this->extraData['artist'][0])) {
            $this->author = $this->extraData['artist'][0];
        }
        if (isset($this->extraData['title'][0])) {
            $this->title = $this->extraData['title'][0];
        }
    }

    private function getInformationToKeep($meta, $mimeType)
    {
        $data = [];
        switch ($mimeType) {
            case 'video/mp4':
            case 'video/quicktime':
                if (isset($meta['comments'])) {
                    $data = $meta['comments'];
                }
                break;
            case 'video/webm':
                break;
            case 'video/ogg':
                break;
            default:
                break;
        }

        if (isset($meta['playtime_seconds'])) {
            $data['playtime_seconds'] = $meta['playtime_seconds'];
        }
        if (isset($meta['video'])) {
            $data['video'] = $meta['video'];
        }
        if (isset($meta['audio'])) {
            $data['audio'] = $meta['audio'];
        }

        $this->cleanData($data);

        return $data;
    }

    private function cleanData(&$data)
    {
        foreach ($data as $key => $value) {
            if ('UndefinedTag' == substr($key, 0, strlen('UndefinedTag'))) {
                unset($data[$key]);
                continue;
            }
            if (is_array($value)) {
                $this->cleanData($value);
                $data[$key] = $value;
            } else {
                $data[$key] = utf8_encode($value);
            }
        }
    }
}
