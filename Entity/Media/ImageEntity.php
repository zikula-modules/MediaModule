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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Cmfcmf\Module\MediaModule\Metadata\GenericMetadataReader;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ImageEntity extends AbstractFileEntity
{
    public function onNewFile(array $info): void
    {
        parent::onNewFile($info);

        $meta = GenericMetadataReader::readMetadata($info['filePath']);
        $this->extraData = $this->getInformationToKeep($meta, $meta['mime_type']);

        if (isset($this->extraData['exif']['IFD0']['Artist'])) {
            $this->author = $this->extraData['exif']['IFD0']['Artist'];
        }
    }

    private function getInformationToKeep(array $meta, string $mimeType): array
    {
        $data = [];

        $setIfSet = function ($result, $keys) use ($meta, &$data) {
            $v = $meta;
            foreach ($keys as $key) {
                if (!isset($v[$key])) {
                    return;
                }
                $v = $v[$key];
            }
            $data[$result] = $v;
        };

        $setIfSet('resolution_x', ['video', 'resolution_x']);
        $setIfSet('resolution_y', ['video', 'resolution_y']);
        $setIfSet('compression_ratio', ['video', 'compression_ratio']);
        $setIfSet('comments', ['comments']);

        switch ($mimeType) {
            case 'image/jpeg':
                if (isset($meta['jpg']['exif']['IFD0'])) {
                    $data['exif']['IFD0'] = $meta['jpg']['exif']['IFD0'];
                }
                if (isset($meta['jpg']['exif']['EXIF'])) {
                    $exif = $meta['jpg']['exif']['EXIF'];
                    // Delete some (big) garbage.
                    unset($exif['MakerNote'], $exif['UserComment']);
                    $data['exif']['EXIF'] = $exif;
                }
                break;
            default:
                break;
        }

        $this->cleanData($data);

        return $data;
    }

    private function cleanData(&$data): void
    {
        foreach ($data as $key => $value) {
            if ('UndefinedTag' === mb_substr((string) $key, 0, mb_strlen('UndefinedTag'))) {
                unset($data[$key]);
                continue;
            }
            if (is_array($value)) {
                $this->cleanData($value);
                $data[$key] = $value;
            } else {
                $data[$key] = utf8_encode((string) $value);
            }
        }
    }
}
