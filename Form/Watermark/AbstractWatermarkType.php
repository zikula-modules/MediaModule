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

class AbstractWatermarkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('title', 'text', [
                'attr' => [
                    'help' => $this->__('A title for you to recognize the watermark.')
                ]
            ])
            ->add('positionX', 'number', [
                'scale' => 0,
                'attr' => [
                    'help' => $this->__('The x position of the watermark inside the picture. Negative numbers will position it right aligned.')
                ]
            ])
            ->add('positionY', 'number', [
                'scale' => 0,
                'attr' => [
                    'help' => $this->__('The y position of the watermark inside the picture. Negative numbers will position it bottom aligned.')
                ]
            ])
            ->add('minSizeX', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->__('Smaller images will not be watermarked.')
                ]
            ])
            ->add('minSizeY', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->__('Smaller images will not be watermarked.')
                ]
            ])
            ->add('relativeSize', 'number', [
                'scale' => 0,
                'required' => false,
                'attr' => [
                    'help' => $this->__('The size of the watermark in percent. If set, it will resize the watermark accordingly.')
                ]
            ])
            ->add('submit', 'submit', [
                'label' => $this->__('Save')
            ])
        ;
    }
}
