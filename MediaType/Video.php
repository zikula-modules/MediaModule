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

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Metadata\GenericMetadataReader;
use Symfony\Component\HttpFoundation\File\File;

class Video extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Video', [], $this->domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-video-o';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Video:fullpage.html.twig', ['entity' => $entity, 'width' => '100%', 'height' => '400']);
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        $meta = [];
        $extraData = $entity->getExtraData();
        if (isset($extraData['title'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Title', [], $this->domain),
                'value' => $extraData['title'][0]
            ];
        }
        if (isset($extraData['creation_date'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Year', [], $this->domain),
                'value' => $extraData['creation_date'][0]
            ];
        }
        if (isset($extraData['genre'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Genre', [], $this->domain),
                'value' => $extraData['genre'][0]
            ];
        }
        if (isset($extraData['playtime_seconds'])) {
            $meta[] = [
                'title' => $this->translator->trans('Duration', [], $this->domain),
                'value' => $this->formatDuration($extraData['playtime_seconds'])
            ];
        }
        if (isset($extraData['video'])) {
            $video = $extraData['video'];

            if (isset($video['resolution_x']) && isset($video['resolution_y'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Resolution', [], $this->domain),
                    'value' => $video['resolution_x'] . " x " . $video['resolution_y']
                ];
            }
            if (isset($video['frame_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Frame rate', [], $this->domain),
                    'value' => (int)$video['frame_rate']
                ];
            }
            if (isset($video['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Video bit rate', [], $this->domain),
                    'value' => (int)$video['bitrate']
                ];
            }
        }
        if (isset($extraData['audio'])) {
            $audio = $extraData['audio'];

            if (isset($audio['channels'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio channels', [], $this->domain),
                    'value' => $audio['channels']
                ];
            }
            if (isset($audio['sample_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio sample rate', [], $this->domain),
                    'value' => $audio['sample_rate']
                ];
            }
            if (isset($audio['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio bit rate', [], $this->domain),
                    'value' => $audio['bitrate']
                ];
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(File $file)
    {
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 5;
        }
        if ($file->getMimeType() == 'application/ogg') {
            // This could be a video or audio file.
            $meta = GenericMetadataReader::readMetadata($file->getPathname());
            if (isset($meta['video']['dataformat'])) {
                return 5;
            }
        }

        return 0;
    }

    /**
     * @return array A list of supported mime types.
     */
    private function getSupportedMimeTypes()
    {
        return [
            'video/mp4',
            'video/webm',
            'video/ogg',
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
        return false;
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        switch ($size) {
            case 'small':
                $width = 200;
                $height = (int)($width / 16 * 9);
                break;
            case 'medium':
                $width = 500;
                $height = (int)($width / 16 * 9);
                break;
            case 'full':
            default:
                $width = "100%";
                $height = 400;
        }

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Video:fullpage.html.twig', ['entity' => $entity, 'width' => $width, 'height' => $height]);
    }

    private function formatDuration($seconds)
    {
        $seconds = (int)$seconds;
        $minutes = (int)($seconds / 60);
        $seconds -= $minutes * 60;
        if ($seconds < 10) {
            $seconds = "0" . $seconds;
        }

        $time = "$minutes:$seconds";

        return $this->translator->trans("%s min", ['%s' => $time], $this->domain);
    }
}
