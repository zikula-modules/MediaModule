<?php

namespace Cmfcmf\Module\MediaModule\Form\Watermark;

use Cmfcmf\Module\MediaModule\Entity\Watermark\ImageWatermarkEntity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ImageWatermarkType extends AbstractWatermarkType
{
    /**
     * @var ImageWatermarkEntity
     */
    protected $entity;

    public function __construct(ImageWatermarkEntity $entity = null)
    {
        parent::__construct();
        $this->entity = $entity;
    }

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
                    'help' => $this->__('Image to be used as watermark.')
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
