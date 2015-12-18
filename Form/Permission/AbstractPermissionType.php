<?php

namespace Cmfcmf\Module\MediaModule\Form\Permission;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractPermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('description', TextareaType::class, [
            'label' => $this->__('Description'),
            'required' => false
        ])->add('position', NumberType::class, [
            'label' => $this->__('Position')
        ])->add('permissionLevel', NumberType::class, [
            'label' => $this->__('Permission level')
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        /** @var \Symfony\Component\OptionsResolver\OptionsResolver $resolver */
        $resolver->setDefault('read_only', true);
    }
}
