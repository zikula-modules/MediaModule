<?php

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
     * @param array $file size, mimeType and name will be set. These values MUST NOT be tested, as they come from the client.
     *
     * @return int 10 if it perfectly matches, 0 if it can't upload.
     */
    public function canUploadArr(array $file);

    public function getOriginalWithWatermark(AbstractFileEntity $entity, $mode, $optimize);
}
