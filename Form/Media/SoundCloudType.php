<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class SoundCloudType extends WebType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['hiddenFields'] = [
            'url', 'license'
        ];
        parent::buildForm($builder, $options);
        $builder
            ->add('musicType', 'hidden')
            ->add('musicId', 'hidden')
        ;
    }
}
