<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\FormBuilderInterface;

class YouTubeType extends WebType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['hiddenFields'] = [
            'url', 'license',  'author', 'authorUrl', 'authorAvatarUrl'
        ];
        parent::buildForm($builder, $options);
    }
}
