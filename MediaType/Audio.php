<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PlaintextEntity;
use Cmfcmf\Module\MediaModule\Metadata\GenericMetadataReader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Audio extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Audio', [], $this->domain);
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
                'title' => $this->translator->trans('Title', [], $this->domain),
                'value' => $extraData['title'][0]
            ];
        }
        if (isset($extraData['album'][0])) {
            $album = $extraData['album'][0];
            if (isset($extraData['year'][0])) {
                $album .= " ({$extraData['year'][0]})";
            }
            $meta[] = [
                'title' => $this->translator->trans('Album', [], $this->domain),
                'value' => $album
            ];
        }
        if (isset($extraData['playtime_seconds'])) {
            $meta[] = [
                'title' => $this->translator->trans('Duration', [], $this->domain),
                'value' => $this->formatDuration($extraData['playtime_seconds'])
            ];
        }
        if (isset($extraData['track_number'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Track number', [], $this->domain),
                'value' => $extraData['track_number'][0]
            ];
        }
        if (isset($extraData['publisher'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Publisher', [], $this->domain),
                'value' => $extraData['publisher'][0]
            ];
        }
        if (isset($extraData['band'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Band', [], $this->domain),
                'value' => $extraData['band'][0]
            ];
        }
        if (isset($extraData['genre'][0])) {
            $meta[] = [
                'title' => $this->translator->trans('Genre', [], $this->domain),
                'value' => $extraData['genre'][0]
            ];
        }
        if (isset($extraData['audio'])) {
            $audio = $extraData['audio'];

            if (isset($audio['channels'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Channels', [], $this->domain),
                    'value' => $audio['channels']
                ];
            }
            if (isset($audio['sample_rate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Sample rate', [], $this->domain),
                    'value' => $audio['sample_rate']
                ];
            }
            if (isset($audio['bitrate'])) {
                $meta[] = [
                    'title' => $this->translator->trans('Bit rate', [], $this->domain),
                    'value' => $audio['bitrate']
                ];
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 5;
        }
        if ($file->getMimeType() == 'application/ogg') {
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
        return in_array($file['mimeType'], $this->getSupportedMimeTypes()) ? 5 : (
        $file['mimeType'] == 'video/ogg' ? 3 : 0
        );
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

        return $this->translator->trans("%s min", ['%s' => $time], $this->domain);
    }
}
