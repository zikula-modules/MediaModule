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

/**
 * A basic but beautiful template using Bootstrap 4 Cards to display media and collections.
 */
class CardsTemplate extends AbstractTemplate
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Cards with thumbnails', [], 'cmfcmfmediamodule');
    }
}
