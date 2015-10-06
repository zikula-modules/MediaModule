<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\ImageEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Image extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->__('Image');
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
                'title' => $this->__('Resolution'),
                'value' => $data['resolution_x'] . ' x ' . $data['resolution_y']
            ];
        }
        if (isset($data['exif'])) {
            if (isset($data['exif']['IFD0'])) {
                if (isset($data['exif']['IFD0']['Model'])) {
                    $meta[] = [
                        'title' => $this->__('Camera'),
                        'value' => $data['exif']['IFD0']['Model']
                    ];
                }/*
                if (isset($data['exif']['IFD0']['Orientation'])) {
                    $meta[] = [
                        'title' => $this->__('Orientation'),
                        'value' => $data['exif']['IFD0']['Orientation'] // @todo Convert to readable output
                    ];
                }*/
            }
            if (isset($data['exif']['EXIF'])) {
                if (isset($data['exif']['EXIF']['FNumber'])) {
                    $meta[] = [
                        'title' => $this->__('Aperture'),
                        'value' => "f/" . $data['exif']['EXIF']['FNumber']
                    ];
                }
                if (isset($data['exif']['EXIF']['ISOSpeedRatings'])) {
                    $meta[] = [
                        'title' => $this->__('ISO value'),
                        'value' => $data['exif']['EXIF']['ISOSpeedRatings']
                    ];
                }
                if (isset($data['exif']['EXIF']['ShutterSpeedValue'])) {
                    $meta[] = [
                        'title' => $this->__('Shutter speed'),
                        'value' => "f/" . $data['exif']['EXIF']['ShutterSpeedValue']
                    ];
                }
                if (isset($data['exif']['EXIF']['Flash'])) {
                    $meta[] = [
                        'title' => $this->__('Flash'),
                        'value' => $this->didFlashFire($data['exif']['EXIF']['Flash']) ? $this->__('on') : $this->__('off')
                    ];
                }
                if (isset($data['exif']['EXIF']['FocalLength'])) {
                    $meta[] = [
                        'title' => $this->__('Focal length'),
                        'value' => $data['exif']['EXIF']['FocalLength'] . " mm"
                    ];
                }
                if (isset($data['exif']['EXIF']['MeteringMode'])) {
                    $meta[] = [
                        'title' => $this->__('Metering mode'),
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
    public function canUpload(UploadedFile $file)
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
     * Whether or not this media type supports uploading the file represented by the file info array.
     *
     * @param array $file
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload.
     */
    public function canUploadArr(array $file)
    {
        return in_array($file['mimeType'], $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var ImageEntity $entity */
        if (!in_array($mode, ['inset', 'outbound'])) {
            throw new \InvalidArgumentException('Invalid mode requested.');
        }

        $this->imagineManager->setPreset(
            $this->getPreset($entity, $entity->getPath(), $width, $height, $mode, $optimize)
        );

        $path = $this->imagineManager->getThumb($entity->getPath(), $entity->getImagineId());
        $path = $this->imagineManager->getThumb($entity->getPath(), $entity->getImagineId());

        $url = \System::getBaseUri() . '/' . $path;
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
                // @todo Should be original size
                $code = $this->getThumbnail($entity, 1000, 1000, 'html', 'inset');
                break;
            case 'medium':
                $code = $this->getThumbnail($entity, 550, 350, 'html', 'inset');
                break;
            case 'small':
                $code = $this->getThumbnail($entity, 250, 150, 'html', 'inset');
                break;
        }
        if ($entity->getAttribution() != null) {
            $code .= '<p>' . $entity->getAttribution() . '</p>';
        }

        return $code;
    }

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize = true)
    {
        return $this->getThumbnail($entity, 'original', 'original', $mode, 'outbound', $optimize);
    }
}
