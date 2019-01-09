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

use Cmfcmf\Module\MediaModule\Form\CollectionTemplate\LightGalleryType;

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
        return $this->translator->trans('Light gallery image grid', [], 'cmfcmfmediamodule');
    }

    public function getSettingsForm()
    {
        return LightGalleryType::class;
    }

    public function getDefaultOptions()
    {
        return [
            'thumbHeight' => 150,
            'thumbWidth' => 200,
            'thumbMode' => 'inset',
            'showTitleBelowThumbs' => false,
            'showAttributionBelowThumbs' => true
        ];
    }
}
