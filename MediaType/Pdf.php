<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PdfEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\ImageEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Pdf extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('PDF', [], $this->domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-pdf-o';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var PdfEntity $entity */
        return '<embed src="' . $entity->getUrl() . '" width="100%" height="600" type="application/pdf">';
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
        return in_array($file->getMimeType(), $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    /**
     * @return array A list of supported mime types.
     */
    private function getSupportedMimeTypes()
    {
        return [
            'application/pdf',
            'application/x-pdf',
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
        //if (!class_exists('Imagick')) {
            return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize, 'pdf');
        //} else {
            // Not yet implemented.
            //throw new \LogicException();
        //}
    }

    public function isEmbeddable()
    {
        return false;
    }
}
