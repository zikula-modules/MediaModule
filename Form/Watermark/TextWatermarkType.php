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

namespace Cmfcmf\Module\MediaModule\Form\Watermark;

use Cmfcmf\Module\MediaModule\Form\Type\ColorType;
use Cmfcmf\Module\MediaModule\Form\Type\FontType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for a text watermark.
 */
class TextWatermarkType extends AbstractWatermarkType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'help' => 'Text to be used as watermark.'
            ])
            ->add('absoluteSize', NumberType::class, [
                'scale' => 0,
                'label' => 'Font size',
                'required' => false,
                'help' => 'The font size to use, regardless of the image size. Either this or the "Relative size" option must be set.'
            ])
            ->add('font', FontType::class, [
                'label' => 'Font'
            ])
            ->add('fontColor', ColorType::class, [
                'label' => 'Font color',
                'help' => 'R-G-B-A'
            ])
            ->add('backgroundColor', ColorType::class, [
                'label' => 'Background color',
                'help' => 'R-G-B-A'
            ])
        ;
        parent::buildForm($builder, $options);
    }
}
