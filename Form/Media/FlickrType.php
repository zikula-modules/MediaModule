<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class FlickrType extends WebType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('flickrid', 'hidden')
            ->add('flickrfarm', 'hidden')
            ->add('flickrsecret', 'hidden')
            ->add('flickrserver', 'hidden')
        ;
    }
}
