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

namespace Cmfcmf\Module\MediaModule\Form\License;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', TextType::class, [
                'disabled' => $options['isEdit'],
                'label' => 'License ID',
                'help' => 'You won\'t be able to change the ID after creation. It should be something like "gplv3" or similar.'
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
                'help' => 'The title of the license to use for displaying it.'
            ])
            ->add('url', UrlType::class, [
                'label' => 'Url',
                'required' => false,
                'help' => 'The place where you can look up the license text.'
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => 'Image Url',
                'required' => false,
                'help' => 'Optional url of a small license icon.'
            ])
            ->add('outdated', CheckboxType::class, [
                'label' => 'Outdated',
                'required' => false,
                'help' => 'Marks a license as outdated to give a visual hint while uploading.'
            ])
            ->add('enabledForUpload', CheckboxType::class, [
                'label' => 'Allow to use for uploads',
                'required' => false,
                'help' => 'If you check this box, you will be able to upload media and license it under this license.'
            ])
            ->add('enabledForWeb', CheckboxType::class, [
                'label' => 'Allow to use for web embeds',
                'required' => false,
                'help' => 'If you check this box, you will be able to embed media from the web using this license.'
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
                'isEdit' => false
            ])
            ->setAllowedTypes('isEdit', 'bool')
        ;
    }
}
