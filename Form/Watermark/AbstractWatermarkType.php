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

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('title', TextType::class, [
                'help' => 'A title for you to recognize the watermark.'
            ])
            ->add('positionX', NumberType::class, [
                'scale' => 0,
                'help' => 'The x position of the watermark inside the picture. Negative numbers will position it right aligned.'
            ])
            ->add('positionY', NumberType::class, [
                'scale' => 0,
                'help' => 'The y position of the watermark inside the picture. Negative numbers will position it bottom aligned.'
            ])
            ->add('minSizeX', NumberType::class, [
                'label' => 'Minimum size x',
                'scale' => 0,
                'required' => false,
                'help' => 'Smaller images will not be watermarked.'
            ])
            ->add('minSizeY', NumberType::class, [
                'label' => 'Minimum size y',
                'scale' => 0,
                'required' => false,
                'help' => 'Smaller images will not be watermarked.'
            ])
            ->add('relativeSize', PercentType::class, [
                'label' => 'Relative size',
                'scale' => 0,
                'type' => 'integer',
                'required' => false,
                'help' => 'The size of the watermark in percent. If set, it will resize the watermark accordingly.'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entity' => null
            ])
        ;
    }
}
