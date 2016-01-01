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

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for a watermark.
 */
class AbstractWatermarkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('title', 'text', [
                'attr' => [
                    'help' => $this->translator->trans('A title for you to recognize the watermark.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('positionX', 'number', [
                'scale' => 0,
                'attr' => [
                    'help' => $this->translator->trans('The x position of the watermark inside the picture. Negative numbers will position it right aligned.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('positionY', 'number', [
                'scale' => 0,
                'attr' => [
                    'help' => $this->translator->trans('The y position of the watermark inside the picture. Negative numbers will position it bottom aligned.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('minSizeX', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('Smaller images will not be watermarked.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('minSizeY', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('Smaller images will not be watermarked.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('relativeSize', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('The size of the watermark in percent. If set, it will resize the watermark accordingly.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('submit', 'submit', [
                'label' => $this->translator->trans('Save', [], 'cmfcmfmediamodule')
            ])
        ;
    }
}
