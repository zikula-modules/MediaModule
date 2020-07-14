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

namespace Cmfcmf\Module\MediaModule\Form\Type\Hook;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;

class EditLicenseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $choices = [];
        $preferredChoices = [];
        $selectedChoices = [];

        foreach ($options['preferredLicenses'] as $license) {
            $choices[$license['title']] = $license['id'];
            if (in_array($license['id'], $options['selectedLicenses'], true)) {
                $selectedChoices[] = $license['id'];
            }
            $preferredChoices[] = $license['id'];
        }

        foreach ($options['outdatedLicenses'] as $license) {
            $choices[$license['title']] = $license['id'];
            if (in_array($license['id'], $options['selectedLicenses'], true)) {
                $selectedChoices[] = $license['id'];
            }
        }

        $builder
            ->add('license', ChoiceType::class, [
                'multiple' => true,
                'label' => 'Select license',
                'placeholder' => 'Unknown',
                'choices' => /** @Ignore */$choices,
                'preferred_choices' => /** @Ignore */$preferredChoiecs,
                'data' => $selectedChoices
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_hook_editlicense';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('selectedLicenses');
        $resolver->setDefined('preferredLicenses');
        $resolver->setDefined('outdatedLicenses');
        $resolver->setAllowedTypes('selectedLicenses', 'array');
        $resolver->setAllowedTypes('preferredLicenses', 'array');
        $resolver->setAllowedTypes('outdatedLicenses', 'array');
    }
}
