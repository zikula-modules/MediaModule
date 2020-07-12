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
use Cmfcmf\Module\MediaModule\Entity\Media\ImageEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PdfEntity;
use Symfony\Component\HttpFoundation\File\File;

class Pdf extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('PDF', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file-pdf';
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
    public function canUpload(File $file)
    {
        return in_array($file->getMimeType(), $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    /**
     * @return array a list of supported mime types
     */
    private function getSupportedMimeTypes()
    {
        return [
            'application/pdf',
            'application/x-pdf',
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
        //if (!class_exists('Imagick')) {
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize, 'pdf');
        //} else {
            // Not yet implemented.
            //throw new \LogicException();
        //}
    }
}
