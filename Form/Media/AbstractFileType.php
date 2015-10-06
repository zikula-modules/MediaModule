<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class AbstractFileType extends AbstractMediaEntityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('file', 'file', [
                'label' => $this->__('Change file'),
                'mapped' => false,
                'required' => false
            ])
            ->add('downloadAllowed', 'checkbox', [
                'required' => false,
                'label' => $this->__('Allow download')
            ])
        ;
    }
}
