<?php

namespace Cmfcmf\Module\MediaModule\Form\CollectionTemplate;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LightGalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('thumbWidth', 'Symfony\Component\Form\Extension\Core\Type\NumberType', [
            'label' => 'Thumbnail width',
            'required' => true
        ])->add('thumbHeight', 'Symfony\Component\Form\Extension\Core\Type\NumberType', [
            'label' => 'Thumbnail height',
            'required' => true
        ])->add('thumbMode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => 'Thumbnail mode',
            'required' => true,
            'choices' => [
                'inset' => 'inset',
                'outbound' => 'outbound'
            ],
        ])->add('showTitleBelowThumbs', 'checkbox', [
            'label' => 'Show the image titles below thumbnails.',
            'required' => false
        ])->add('showAttributionBelowThumbs', 'checkbox', [
            'label' => 'Show the image attributions below thumbnails.',
            'required' => false
        ]);
    }
}