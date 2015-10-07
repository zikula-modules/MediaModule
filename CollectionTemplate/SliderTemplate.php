<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class SliderTemplate extends AbstractTemplate
{
    public function getTitle()
    {
        return $this->translator->trans('Big thumbnail slider', [], $this->domain);
    }
}
