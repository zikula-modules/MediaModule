<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\ImageEntity;
use Symfony\Component\HttpFoundation\File\File;

class Image extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Image', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-image';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var ImageEntity $entity */
        $url = $this->getOriginalWithWatermark($entity, 'url', true);
        $url = htmlentities($url);

        return "<img class=\"img-responsive\" src=\"$url\" />";
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        $data = $entity->getExtraData();
        $meta = [];
        if (isset($data['resolution_x']) && isset($data['resolution_y'])) {
            $meta[] = [
                'title' => $this->translator->trans('Resolution', [], 'cmfcmfmediamodule'),
                'value' => $data['resolution_x'] . ' x ' . $data['resolution_y']
            ];
        }
        if (isset($data['exif'])) {
            if (isset($data['exif']['IFD0'])) {
                if (isset($data['exif']['IFD0']['Model'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Camera', [], 'cmfcmfmediamodule'),
                        'value' => $data['exif']['IFD0']['Model']
                    ];
                }/*
                if (isset($data['exif']['IFD0']['Orientation'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Orientation', [], 'cmfcmfmediamodule'),
                        'value' => $data['exif']['IFD0']['Orientation'] // @todo Convert to readable output
                    ];
                }*/
            }
            if (isset($data['exif']['EXIF'])) {
                if (isset($data['exif']['EXIF']['FNumber'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Aperture', [], 'cmfcmfmediamodule'),
                        'value' => "f/" . $data['exif']['EXIF']['FNumber']
                    ];
                }
                if (isset($data['exif']['EXIF']['ISOSpeedRatings'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('ISO value', [], 'cmfcmfmediamodule'),
                        'value' => $data['exif']['EXIF']['ISOSpeedRatings']
                    ];
                }
                if (isset($data['exif']['EXIF']['ShutterSpeedValue'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Shutter speed', [], 'cmfcmfmediamodule'),
                        'value' => "f/" . $data['exif']['EXIF']['ShutterSpeedValue']
                    ];
                }
                if (isset($data['exif']['EXIF']['Flash'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Flash', [], 'cmfcmfmediamodule'),
                        'value' => $this->didFlashFire($data['exif']['EXIF']['Flash'])
                            ? $this->translator->trans('on', [], 'cmfcmfmediamodule')
                            : $this->translator->trans('off', [], 'cmfcmfmediamodule')
                    ];
                }
                if (isset($data['exif']['EXIF']['FocalLength'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Focal length', [], 'cmfcmfmediamodule'),
                        'value' => $data['exif']['EXIF']['FocalLength'] . " mm"
                    ];
                }
                if (isset($data['exif']['EXIF']['MeteringMode'])) {
                    $meta[] = [
                        'title' => $this->translator->trans('Metering mode', [], 'cmfcmfmediamodule'),
                        'value' => $this->convertMeteringMode($data['exif']['EXIF']['MeteringMode'])
                    ];
                }
            }
        }

        return $meta;
    }

    private function didFlashFire($flash)
    {
        // 0:  FlashDidNotFire
        // 1:  FlashFired
        // 2:  StrobeReturnLightDetected
        // 4:  StrobeReturnLightNotDetected
        // 8:  CompulsoryFlashMode
        // 16: AutoMode
        // 32: NoFlashFunction
        // 64: RedEyeReductionMode

        return ($flash & 1) != 0;
    }

    private function convertMeteringMode($meteringMode)
    {
        $conversion = [
            0 => 'Unknown',
            1 => 'Average',
            2 => 'CenterWeightedAverage',
            3 => 'Spot',
            4 => 'MultiSpot',
            5 => 'Pattern',
            6 => 'Partial',
            255 => 'other'
        ];

        return array_key_exists($meteringMode, $conversion) ? $conversion[$meteringMode] : $conversion[255];
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(File $file)
    {
        return in_array($file->getMimeType(), $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    /**
     * @return array A list of supported mime types.
     */
    private function getSupportedMimeTypes()
    {
        return [
            'image/gif',
            'image/jpeg',
            'image/png',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function mightUpload($mimeType, $size, $name)
    {
        return in_array($mimeType, $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var ImageEntity $entity */
        if (!in_array($mode, ['inset', 'outbound'])) {
            throw new \InvalidArgumentException('Invalid mode requested.');
        }

        /** TODO migrate
        $this->imagineManager->setPreset(
            $this->getPreset($entity, $entity->getPath(), $width, $height, $mode, $optimize)
        );
        */

        $path = $this->imagineCacheManager->getBrowserPath($entity->getPath(), 'zkroot'/** TODO, $entity->getImagineId()*/);

        $url = $this->getBaseUri() . '/' . $path;
        switch ($format) {
            case 'url':
                return $url;
            case 'html':
                return '<img src="' . $url . '" />';
            case 'path':
                return $path;
        }
        throw new \LogicException();
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        /** @var ImageEntity $entity */
        switch ($size) {
            default:
            case 'full':
                $code = $this->getOriginalWithWatermark($entity, 'html');
                break;
            case 'medium':
                $code = $this->getThumbnail($entity, 550, 350, 'html', 'inset');
                break;
            case 'small':
                $code = $this->getThumbnail($entity, 250, 150, 'html', 'inset');
                break;
        }
        if (null !== $entity->getAttribution()) {
            $code .= '<p>' . $this->__f('By %s', ['%s' => $entity->getAttribution()], 'cmfcmfmediamodule') . '</p>';
        }

        return $code;
    }

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize = true)
    {
        return $this->getThumbnail($entity, 'original', 'original', $mode, 'outbound', $optimize);
    }
}
