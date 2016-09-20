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
 * Displays a light gallery http://sachinchoolur.github.io/lightGallery/.
 */
class LightGalleryTemplate extends AbstractTemplate
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Light gallery', [], 'cmfcmfmediamodule');
    }
}
