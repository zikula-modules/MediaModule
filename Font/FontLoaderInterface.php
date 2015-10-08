<?php

namespace Cmfcmf\Module\MediaModule\Font;

interface FontLoaderInterface
{
    /**
     * @return FontInterface[]
     */
    public function loadFonts();
}
