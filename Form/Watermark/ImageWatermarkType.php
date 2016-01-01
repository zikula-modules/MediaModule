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

use Cmfcmf\Module\MediaModule\Entity\Watermark\ImageWatermarkEntity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for an image watermark.
 */
class ImageWatermarkType extends AbstractWatermarkType
{
    /**
     * @var ImageWatermarkEntity
     */
    protected $entity;

    /**
     * @param ImageWatermarkEntity|null $entity
     */
    public function __construct(ImageWatermarkEntity $entity = null)
    {
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $file = null;
        if ($this->entity !== null) {
            $file = new File($this->entity->getPath());
        }
        $builder
            ->add('file', 'file', [
                'multiple' => false,
                'mapped' => false,
                'attr' => [
                    'help' => $this->translator->trans('Image to be used as watermark.', [], 'cmfcmfmediamodule')
                ],
                'data' => $file, // @todo Still needed??
                'required' => $this->entity === null,
                'constraints' => [
                    new Assert\File([
                        // NOTE: If you change the allowed mime types here, make sure to
                        // also change them in {@link ImageWatermarkEntity}
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif'
                        ]
                    ])
                ]
            ])
        ;
        parent::buildForm($builder, $options);
    }
}
