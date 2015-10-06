<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class WebType extends AbstractMediaEntityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('url', isset($options['hiddenFields']) && in_array('url', $options['hiddenFields']) ? 'hidden' : 'url')
        ;
    }
}
