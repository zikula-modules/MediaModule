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
use Cmfcmf\Module\MediaModule\Entity\Media\ArchiveEntity;
use Symfony\Component\HttpFoundation\File\File;

class Archive extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('File archive', [], 'cmfcmfmediamodule');
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
        return $this->renderEngine->render('CmfcmfMediaModule:MediaType/Archive:fullpage.html.twig', ['entity' => $entity]);
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        /** @var ArchiveEntity $entity */
        $meta = [];
        if (false !== $entity->getNumberOfFiles()) {
            $meta[] = [
                'title' => $this->translator->trans('Contained files', [], 'cmfcmfmediamodule'),
                'value' => $entity->getNumberOfFiles()
            ];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function canUpload(File $file)
    {
        return in_array($file->getMimeType(), $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function mightUpload($mimeType, $size, $name)
    {
        return in_array($mimeType, $this->getSupportedMimeTypes()) ? 5 : 0;
    }

    /**
     * @return array a list of supported mime types
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

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var ArchiveEntity $entity */
        $extension = false;
        if (in_array($entity->getMimeType(), ['application/x-gzip', 'application/x-tar', 'application/x-gtar'])) {
            $extension = 'tgz';
        }

        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize, $extension);
    }

    public function isEmbeddable()
    {
        return false;
    }
}
