<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;

interface PasteMediaTypeInterface
{
    /**
     * Checks whether or not the media type matches the pasted text. Can return something in between 0 (does not match)
     * and 10.
     *
     * @param $pastedText
     *
     * @return int
     */
    public function matchesPaste($pastedText);

    /**
     * @param string $pastedText
     *
     * @return AbstractMediaEntity
     */
    public function getEntityFromPaste($pastedText);
}
