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

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class AbstractFileType extends AbstractMediaEntityType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('file', FileType::class, [
                'label' => $this->translator->trans('Change file', [], 'cmfcmfmediamodule'),
                'mapped' => false,
                'required' => false
            ])
            ->add('downloadAllowed', CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Allow download', [], 'cmfcmfmediamodule')
            ])
        ;
    }
}
