<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class CardsTemplate extends AbstractTemplate
{
    public function getTitle()
    {
        return $this->translator->trans('Cards with thumbnails', [], $this->domain);
    }
}
