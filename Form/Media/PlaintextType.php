<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class PlaintextType extends AbstractFileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('useSyntaxHighlighting', 'checkbox', [
                'required' => false,
                'label' => $this->__('Use syntax highlighting')
            ])
        ;
    }
}
