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
use Cmfcmf\Module\MediaModule\Entity\Media\PlaintextEntity;
use Cmfcmf\Module\MediaModule\Metadata\GenericMetadataReader;
use Symfony\Component\HttpFoundation\File\File;

class Audio extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Audio', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-audio-o';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Audio:fullpage.html.twig', ['entity' => $entity]);
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
        if (isset($extraData['album'][0])) {
            $album = $extraData['album'][0];
            if (isset($extraData['year'][0])) {
                $album .= " ({$extraData['year'][0]})";
            }
            $meta[] = [
                'title' => $this->translator->trans('Album', [], 'cmfcmfmediamodule'),
                'value' => $album
            ];
        }
        if (isset($extraData['playtime_seconds'])) {
            $meta[] = [
                'title' => $this->translator->trans('Duration', [], 'cmfcmfmediamodule'),
                'value' => $this->formatDuration($extraData['playtime_seconds'])
            ];
        }
        if (isset($extraData['track_number'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Track number', [], 'cmfcmfmediamodule'),
                'value' => $extraData['track_number'][0]
            ];
        }
        if (isset($extraData['publisher'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Publisher', [], 'cmfcmfmediamodule'),
                'value' => $extraData['publisher'][0]
            ];
        }
        if (isset($extraData['band'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Band', [], 'cmfcmfmediamodule'),
                'value' => $extraData['band'][0]
            ];
        }
        if (isset($extraData['genre'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Genre', [], 'cmfcmfmediamodule'),
                'value' => $extraData['genre'][0]
            ];
        }
        if (isset($extraData['audio'])) {
            $audio = $extraData['audio'];

            if (isset($audio['channels'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Channels', [], 'cmfcmfmediamodule'),
                    'value' => $audio['channels']
                ];
            }
            if (isset($audio['sample_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Sample rate', [], 'cmfcmfmediamodule'),
                    'value' => $audio['sample_rate']
                ];
            }
            if (isset($audio['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Bit rate', [], 'cmfcmfmediamodule'),
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
        if ($file->getMimeType() == 'application/ogg' || ($file->getMimeType() == 'application/octet-stream' && $file->getClientOriginalExtension() == 'mp3')) {
            // This could be a video or audio file.
            $meta = GenericMetadataReader::readMetadata($file->getPathname());
            if (isset($meta['audio']['dataformat'])) {
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
            'audio/ogg',
            'audio/wav',
            'audio/x-wav',
            'audio/mpeg',
            'audio/mp3'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function mightUpload($mimeType, $size, $name)
    {
        return in_array($mimeType, $this->getSupportedMimeTypes()) ? 5 : ($mimeType == 'video/ogg' ? 3 : 0);
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var PlaintextEntity $entity */
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize);
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        return $this->renderFullpage($entity);
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

        return $this->translator->trans("%s min", ['%s' => $time], 'cmfcmfmediamodule');
    }
}
