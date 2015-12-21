<?php

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Cmfcmf\Module\MediaModule\Font\FontCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FontType extends AbstractType
{
    /**
     * @var FontCollection
     */
    private $fontCollection;

    public function __construct(FontCollection $fontCollection)
    {
        $this->fontCollection = $fontCollection;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'choices' => $this->fontCollection->getFontsForForm()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fontUrl'] = $this->fontCollection->getFontUrl();
    }

    public function getParent()
    {
        return 'choice';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'cmfcmfmediamodule_font_choice';
    }
}
