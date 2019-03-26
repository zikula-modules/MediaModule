<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Importer;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ServerDirectoryType extends AbstractImporterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serverDirectory', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('Server directory', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'help' => $this->translator->trans('Either provide an absolute path or a path relative to the Zikula root directory.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('includeSubDirectories', CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Include sub directories', [], 'cmfcmfmediamodule')
            ])
            ->add('createSubCollectionsForSubDirectories', CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Create sub collections for sub directories', [], 'cmfcmfmediamodule')
            ])
        ;
    }
}
