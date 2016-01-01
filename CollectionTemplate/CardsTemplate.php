<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

class CardsTemplate extends AbstractTemplate
{
    public function getTitle()
    {
        return $this->translator->trans('Cards with thumbnails', [], $this->domain);
    }
}
