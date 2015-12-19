<?php

namespace Cmfcmf\Module\MediaModule\Form\Permission;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

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
        ])->add('permissionLevel', PermissionLevelType::class, [
            'label' => $this->__('Permission level')
        ]);
    }
}
