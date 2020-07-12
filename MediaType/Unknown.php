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

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Symfony\Component\HttpFoundation\File\File;

class Unknown extends AbstractFileMediaType implements UploadableMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('File', [], 'cmfcmfmediamodule');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-file';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        return '';
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
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function mightUpload($mimeType, $size, $name)
    {
        return 1;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound', $optimize = true)
    {
        /** @var AbstractFileEntity $entity */
        return $this->getIconThumbnailByFileExtension($entity, $width, $height, $format, $mode, $optimize, false);
    }

    public function isEmbeddable()
    {
        return false;
    }
}
