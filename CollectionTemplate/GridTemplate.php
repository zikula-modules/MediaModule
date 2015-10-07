<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class GridTemplate extends AbstractTemplate
{
    public function getTitle()
    {
        return $this->translator->trans('Seamless and responsive grid', [], $this->domain);
    }
}
