<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Watermark;

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
            ->add('text', 'text', [
                'attr' => [
                    'help' => $this->translator->trans('Text to be used as watermark.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('absoluteSize', 'number', [
                'scale' => 0,
                'label' => $this->translator->trans('Font size', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('The font size to use, reagardless of the image size. Either this or the "Relative size" option must be set.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('font', 'cmfcmfmediamodule_font_choice', [
                'label' => $this->translator->trans('Font', [], 'cmfcmfmediamodule')
            ])
        ;
        parent::buildForm($builder, $options);
    }
}
