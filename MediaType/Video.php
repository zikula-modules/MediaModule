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
        return $this->translator->trans('Video', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fas fa-file-video';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        $clientID = $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientID');
        $clientSecret = $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientSecret');

        return $this->twig->render('@CmfcmfMediaModule/MediaType/Video/fullpage.html.twig', [
            'entity' => $entity,
            'width' => '100%',
            'height' => '400',
            'enableYouTubeUpload' => !empty($clientID) && !empty($clientSecret)
        ]);
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        $meta = [];
        $extraData = $entity->getExtraData();
        if (isset($extraData['title'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Title', [], 'cmfcmfmediamodule'),
                'value' => $extraData['title'][0]
            ];
        }
        if (isset($extraData['creation_date'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Year', [], 'cmfcmfmediamodule'),
                'value' => $extraData['creation_date'][0]
            ];
        }
        if (isset($extraData['genre'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Genre', [], 'cmfcmfmediamodule'),
                'value' => $extraData['genre'][0]
            ];
        }
        if (isset($extraData['playtime_seconds'])) {
            $meta[] = [
                'title' => $this->translator->trans('Duration', [], 'cmfcmfmediamodule'),
                'value' => $this->formatDuration($extraData['playtime_seconds'])
            ];
        }
        if (isset($extraData['video'])) {
            $video = $extraData['video'];

            if (isset($video['resolution_x'], $video['resolution_y'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Resolution', [], 'cmfcmfmediamodule'),
                    'value' => $video['resolution_x'] . " x " . $video['resolution_y']
                ];
            }
            if (isset($video['frame_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Frame rate', [], 'cmfcmfmediamodule'),
                    'value' => (int) $video['frame_rate']
                ];
            }
            if (isset($video['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Video bit rate', [], 'cmfcmfmediamodule'),
                    'value' => (int) $video['bitrate']
                ];
            }
        }
        if (isset($extraData['audio'])) {
            $audio = $extraData['audio'];

            if (isset($audio['channels'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio channels', [], 'cmfcmfmediamodule'),
                    'value' => $audio['channels']
                ];
            }
            if (isset($audio['sample_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio sample rate', [], 'cmfcmfmediamodule'),
                    'value' => $audio['sample_rate']
                ];
            }
            if (isset($audio['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Audio bit rate', [], 'cmfcmfmediamodule'),
                    'value' => $audio['bitrate']
                ];
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(File $file): int
    {
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 5;
        }
        if ('application/ogg' === $file->getMimeType()) {
            // This could be a video or audio file.
            $meta = GenericMetadataReader::readMetadata($file->getPathname());
            if (isset($meta['video']['dataformat'])) {
                return 5;
            }
        }

        return 0;
    }

    /**
     * @return array a list of supported mime types
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
    public function mightUpload(string $mimeType, int $size, string $name): int
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
                $height = (int) ($width / 16 * 9);
                break;
            case 'medium':
                $width = 500;
                $height = (int) ($width / 16 * 9);
                break;
            case 'full':
            default:
                $width = "100%";
                $height = 400;
        }

        return $this->twig->render('@CmfcmfMediaModule/MediaType/Video/fullpage.html.twig', ['entity' => $entity, 'width' => $width, 'height' => $height]);
    }

    private function formatDuration($seconds)
    {
        $seconds = (int) $seconds;
        $minutes = (int) ($seconds / 60);
        $seconds -= $minutes * 60;
        if ($seconds < 10) {
            $seconds = "0" . $seconds;
        }

        $time = "${minutes}:${seconds}";

        return $this->translator->trans("%s% min", ['%s%' => $time], 'cmfcmfmediamodule');
    }
}
