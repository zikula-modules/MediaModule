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
                'attr' => [
                    'help' => $this->translator->trans('Text to be used as watermark.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('absoluteSize', NumberType::class, [
                'scale' => 0,
                'label' => $this->translator->trans('Font size', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('The font size to use, reagardless of the image size. Either this or the "Relative size" option must be set.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('font', FontType::class, [
                'label' => $this->translator->trans('Font', [], 'cmfcmfmediamodule')
            ])
            ->add('fontColor', ColorType::class, [
                'label' => $this->translator->trans('Font color', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('R-G-B-A', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('backgroundColor', ColorType::class, [
                'label' => $this->translator->trans('Background color', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('R-G-B-A', [], 'cmfcmfmediamodule')
                ]
            ])
        ;
        parent::buildForm($builder, $options);
    }
}
