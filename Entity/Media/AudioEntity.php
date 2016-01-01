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
class AudioEntity extends AbstractFileEntity
{
    public function onNewFile(array $info)
    {
        parent::onNewFile($info);

        $meta = GenericMetadataReader::readMetadata($info['filePath']);
        $this->extraData = $this->getInformationToKeep($meta, $meta['mime_type']);

        if (isset($this->extraData['artist'][0])) {
            $this->author = $this->extraData['artist'][0];
        } elseif (isset($this->extraData['band'][0])) {
            $this->author = $this->extraData['band'][0];
        }
        if (isset($this->extraData['title'][0])) {
            $title = $this->extraData['title'][0];
            if (isset($this->extraData['album'][0])) {
                $title .= ' - ' . $this->extraData['album'][0];
            }
            $this->title = $title;
        }
    }

    private function getInformationToKeep($meta, $mimeType)
    {
        $data = [];
        switch ($mimeType) {
            case 'audio/mpeg':
                if (isset($meta['comments'])) {
                    $data = $meta['comments'];
                    unset($data['file_type']);
                }
                break;
            case 'audio/wav':
            case 'audio/x-wav':
                break;
            case 'audio/ogg':
            case 'application/ogg':
            case 'video/ogg':
                break;
            default:
                break;
        }

        if (isset($meta['playtime_seconds'])) {
            $data['playtime_seconds'] = $meta['playtime_seconds'];
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
            if (substr($key, 0, strlen('UndefinedTag')) == 'UndefinedTag') {
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
