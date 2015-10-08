<?php

namespace Cmfcmf\Module\MediaModule\Form\Watermark;

use Symfony\Component\Form\FormBuilderInterface;

class TextWatermarkType extends AbstractWatermarkType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'text', [
                'attr' => [
                    'help' => $this->__('Text to be used as watermark.')
                ]
            ])
            ->add('absoluteSize', 'number', [
                'scale' => 0,
                'label' => $this->__('Font size'),
                'required' => false,
                'attr' => [
                    'help' => $this->__('The font size to use, reagardless of the image size. Either this or the "Relative size" option must be set.')
                ]
            ])
            ->add('font', 'cmfcmfmediamodule_font_choice', [
                'label' => $this->__('Font')
            ])
        ;
        parent::buildForm($builder, $options);
    }
}
