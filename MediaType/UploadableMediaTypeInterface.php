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

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Symfony\Component\HttpFoundation\File\File;

interface UploadableMediaTypeInterface
{
    /**
     * Whether or not this media type supports uploading the given file.
     *
     * @param File $file
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload.
     */
    public function canUpload(File $file);

    /**
     * Whether or not this media type supports uploading the file represented by the file info array.
     *
     * @param string $mimeType The mime type
     * @param int    $size     The file size
     * @param string $name     The file name
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload.
     */
    public function mightUpload($mimeType, $size, $name);

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize);
}
