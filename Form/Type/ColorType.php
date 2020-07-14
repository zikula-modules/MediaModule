<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Displays a color selector.
 *
 * TODO consider replacing by native color picker
 * check http://symfony.com/doc/current/reference/forms/types/color.html
 * NOTE this would process RGB instead of RGBA values!
 */
class ColorType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_color';
    }
}
