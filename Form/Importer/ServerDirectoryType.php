<?php

namespace Cmfcmf\Module\MediaModule\Form\Importer;

use Symfony\Component\Form\FormBuilderInterface;

class ServerDirectoryType extends AbstractImporterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('serverDirectory', 'text', [
                'required' => true
            ])
            ->add('includeSubDirectories', 'checkbox')
            ->add('createSubCollectionsForSubDirectories', 'checkbox')
        ;
    }
}
