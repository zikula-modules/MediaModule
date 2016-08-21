<?php

namespace Cmfcmf\Module\MediaModule\Form\Importer;

use Symfony\Component\Form\FormBuilderInterface;

class ServerDirectoryType extends AbstractImporterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serverDirectory', 'text', [
                'required' => true,
                'label' => $this->translator->trans('Server directory', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('Either provide an absolute path or a path relative to the Zikula root directory.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('includeSubDirectories', 'checkbox', [
                'required' => false,
                'label' => $this->translator->trans('Include sub directories', [], 'cmfcmfmediamodule')
            ])
            ->add('createSubCollectionsForSubDirectories', 'checkbox', [
                'required' => false,
                'label' => $this->translator->trans('Create sub collections for sub directories', [], 'cmfcmfmediamodule')
            ])
        ;
    }
}
