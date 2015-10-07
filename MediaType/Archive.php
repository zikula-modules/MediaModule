<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\ArchiveEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PlaintextEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Archive extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('File archive', [], $this->domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-archive-o';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var ArchiveEntity $entity */
        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Archive:Fullpage.html.twig', ['entity' => $entity]);
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        /** @var ArchiveEntity $entity */
        $meta = [];
        if ($entity->getNumberOfFiles() !== false) {
            $meta[] = [
                'title' => $this->translator->trans('Contained files', [], $this->domain),
                'value' => $entity->getNumberOfFiles()
            ];
        }
        return $meta;
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
            'application/x-gzip',
            'application/x-tar',
            'application/x-gtar',
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
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
        /** @var AbstractFileEntity $entity */
        $extension = false;
        if (in_array($entity->getMimeType(), ['application/x-gzip', 'application/x-tar', 'application/x-gtar'])) {
            $extension = 'tgz';
        }
        /** @var PlaintextEntity $entity */
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize, $extension);
    }

    public function isEmbeddable()
    {
        return false;
    }
}
