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
 * Displays media in a grid-like layout, with less text than other templates.
 */
class GridTemplate extends AbstractTemplate
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Seamless and responsive grid', [], 'cmfcmfmediamodule');
    }
}
