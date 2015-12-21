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
        ])->add('permissionLevels', 'Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType', [
            'label' => $this->__('Permission level')
        ])->add('validAfter', 'datetime', [
            'label' => $this->__('Valid after'),
            'widget' => 'choice',
            'required' => false
        ])->add('validUntil', 'datetime', [
            'label' => $this->__('Valid until'),
            'widget' => 'choice',
            'required' => false
        ])->add('goOn', 'checkbox', [
            'label' => $this->__('Go on if permission not sufficient'),
            'required' => false
        ]);
    }
}
