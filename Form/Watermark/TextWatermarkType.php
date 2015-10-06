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
            ->add('font', 'choice', [
                'choices' => $this->getFonts(),
                'expanded' => true
            ])
        ;
        parent::buildForm($builder, $options);
    }

    private function getFonts()
    {
        $fonts = [];
        $finder = \Symfony\Component\Finder\Finder::create()
            ->files()
            ->name('*.ttf')
            ->in(__DIR__ . '/../../Resources/fonts')
        ;
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $fontName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $fonts[$file->getFilename()] = $fontName;
        }

        return $fonts;
    }
}
