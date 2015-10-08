<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PdfEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PlaintextEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Plaintext extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Plaintext', [], $this->domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-text-o';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /* @var PdfEntity $entity */
        $file = fopen($entity->getPath(), 'r');
        $content = fread($file, 500000);
        fclose($file);
        // @todo check if EOF.

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Plaintext:Fullpage.html.twig', ['entity' => $entity, 'content' => $content]);
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 4;
        }

        return 0;
    }

    /**
     * @return array A list of supported mime types.
     */
    private function getSupportedMimeTypes()
    {
        return [
            'text/plain',
            'application/json',
            'application/javascript',
            'text/css',
            'text/csv',
            'text/html',
            'text/x-c++'
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
        return in_array($file['mimeType'], $this->getSupportedMimeTypes()) ? 4 : 0;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /* @var PlaintextEntity $entity */
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize);
    }

    public function isEmbeddable()
    {
        return false;
    }
}
