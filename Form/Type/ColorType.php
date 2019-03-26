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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Displays a color selector.
 *
 * TODO consider replacing by native color picker
 * check http://symfony.com/doc/current/reference/forms/types/color.html
 * NOTE this would process RGB instead of RGBA values!
 */
class ColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new CallbackTransformer(
            function ($rgba) {
                if (empty($rgba)) {
                    return '';
                }

                return "#" . mb_substr($rgba, 7, 2) . mb_substr($rgba, 1, 6);
            }, function ($argb) {
                return "#" . mb_substr($argb, 3, 6) . mb_substr($argb, 1, 2);
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_color';
    }
}
