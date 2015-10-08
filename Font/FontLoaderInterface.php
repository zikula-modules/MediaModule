<?php

namespace Cmfcmf\Module\MediaModule\Font;

interface FontLoaderInterface
{
    /**
     * @return Font[]
     */
    public function loadFonts();
}
