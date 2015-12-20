<?php

namespace Cmfcmf\Module\MediaModule\Form\Permission;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractPermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('position', 'number', [
            'label' => $this->__('Position')
        ])->add('description', 'textarea', [
            'label' => $this->__('Description'),
            'required' => false
        ])->add('permissionLevel', 'Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType', [
            'label' => $this->__('Permission level')
        ]);
    }
}
