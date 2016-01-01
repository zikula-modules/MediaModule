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
use Symfony\Component\Form\FormBuilderInterface;

class LicenseType extends AbstractType
{
    /**
     * @var bool
     */
    private $isEdit;

    public function __construct($isEdit)
    {
        parent::__construct();
        $this->isEdit = $isEdit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('id', 'text', [
                'required' => true,
                'disabled' => $this->isEdit,
                'label' => $this->__('License ID'),
                'attr' => [
                    'help' => $this->__('You won\'t be able to change the ID after creation. It should be something like "gplv3" or similar.')
                ]
            ])
            ->add('title', 'text', [
                'label' => $this->__('Title'),
                'required' => true,
                'attr' => [
                    'help' => $this->__('The title of the license to use for displaying it.')
                ]
            ])
            ->add('url', 'url', [
                'label' => $this->__('Url'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('The place where you can look up the license text.')
                ]
            ])
            ->add('imageUrl', 'url', [
                'label' => $this->__('Image Url'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('Optional url of a small license icon.')
                ]
            ])
            ->add('outdated', 'checkbox', [
                'label' => $this->__('Outdated'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('Marks a license as outdated to give a visual hint while uploading.')
                ]
            ])
            ->add('enabledForUpload', 'checkbox', [
                'label' => $this->__('Allow to use for uploads'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('If you check this box, you will be able to upload media and license it under this license.')
                ]
            ])
            ->add('enabledForWeb', 'checkbox', [
                'label' => $this->__('Allow to use for web embeds'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('If you check this box, you will be able to embed media from the web using this license.')
                ]
            ])
            ->add('submit', 'submit', [
                'label' => $this->__('Save')
            ])
        ;
    }
}
