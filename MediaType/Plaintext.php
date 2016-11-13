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
use Cmfcmf\Module\MediaModule\Entity\Media\PdfEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\PlaintextEntity;
use Symfony\Component\HttpFoundation\File\File;

class Plaintext extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Plaintext', [], 'cmfcmfmediamodule');
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
        /** @var PdfEntity $entity */
        $file = fopen($entity->getPath(), 'r');
        $content = fread($file, 500000);
        fclose($file);
        // @todo check if EOF.

        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Plaintext:fullpage.html.twig', ['entity' => $entity, 'content' => $content]);
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
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 4;
        }
        if ($mimeType == 'inode/x-empty' && $file->getExtension() == 'txt') {
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
            'text/x-c++',
            'application/xml',
            'text/xml',
            'application/atom+xml',
            'application/xhtml+xml',
            'application/mathml+xml',
            'application/rss+xml'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function mightUpload($mimeType, $size, $name)
    {
        if (in_array($mimeType, $this->getSupportedMimeTypes())) {
            return 4;
        }
        if ($mimeType == 'inode/x-empty' && pathinfo($name, PATHINFO_EXTENSION) == 'txt') {
            return 4;
        }

        return 0;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var PlaintextEntity $entity */
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize);
    }

    public function isEmbeddable()
    {
        return false;
    }
}
