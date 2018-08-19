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

use Cmfcmf\Module\MediaModule\Form\CollectionTemplate\GalleriaType;

/**
 * Displays a Galleria image slider https://galleria.io/.
 */
class GalleriaTemplate extends AbstractTemplate
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Galleria image slider', [], 'cmfcmfmediamodule');
    }

    public function getSettingsForm()
    {
        return GalleriaType::class;
    }

    public function getDefaultOptions()
    {
        return [
            'height' => 400,
        ];
    }
}
