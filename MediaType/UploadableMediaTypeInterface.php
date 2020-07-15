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
use Symfony\Component\HttpFoundation\File\File;

interface UploadableMediaTypeInterface
{
    /**
     * Whether or not this media type supports uploading the given file.
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload
     */
    public function canUpload(File $file): int;

    /**
     * Whether or not this media type supports uploading the file represented by the file info array.
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload
     */
    public function mightUpload(string $mimeType, int $size, string $name): int;

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize);
}
