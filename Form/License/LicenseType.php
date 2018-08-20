<?php

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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class LicenseType extends AbstractType
{
    /**
     * @var bool
     */
    private $isEdit;

    /**
     * @param bool $isEdit Whether or not the license is currently edited
     */
    public function __construct($isEdit)
    {
        $this->isEdit = $isEdit;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', TextType::class, [
                'disabled' => $this->isEdit,
                'label' => $this->translator->trans('License ID', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('You won\'t be able to change the ID after creation. It should be something like "gplv3" or similar.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('The title of the license to use for displaying it.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('url', UrlType::class, [
                'label' => $this->translator->trans('Url', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('The place where you can look up the license text.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => $this->translator->trans('Image Url', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('Optional url of a small license icon.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('outdated', CheckboxType::class, [
                'label' => $this->translator->trans('Outdated', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('Marks a license as outdated to give a visual hint while uploading.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('enabledForUpload', CheckboxType::class, [
                'label' => $this->translator->trans('Allow to use for uploads', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('If you check this box, you will be able to upload media and license it under this license.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('enabledForWeb', CheckboxType::class, [
                'label' => $this->translator->trans('Allow to use for web embeds', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans('If you check this box, you will be able to embed media from the web using this license.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Save', [], 'cmfcmfmediamodule')
            ])
        ;
    }
}
