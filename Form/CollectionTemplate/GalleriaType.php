<?php

namespace Cmfcmf\Module\MediaModule\Form\CollectionTemplate;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GalleriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('height', 'Symfony\Component\Form\Extension\Core\Type\NumberType', [
            'label' => 'Slider height',
            'required' => true
        ]);
    }
}