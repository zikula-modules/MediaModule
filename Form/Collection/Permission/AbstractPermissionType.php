<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractPermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('permissionLevels', 'Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType', [
            'label' => $this->__('Permission level')
        ])->add('description', 'textarea', [
            'label' => $this->__('Description'),
            'required' => false,
            'attr' => [
                'help' => $this->__('This is just for you to remember why you created this permission.')
            ]
        ])->add('appliedToSelf', 'checkbox', [
            'label' => $this->__('Applies to the collection itself'),
            'required' => false
        ])->add('appliedToSubCollections', 'checkbox', [
            'label' => $this->__('Applies to sub-collections'),
            'required' => false
        ])->add('goOn', 'checkbox', [
            'label' => $this->__('Go on if this permission is not sufficient'),
            'required' => false
        ])->add('validAfter', 'datetime', [
            'label' => $this->__('Valid after'),
            'widget' => 'choice',
            'required' => false,
            'attr' => [
                'help' => $this->__('If you specify a date, the permission rule will only be taken into account after the specified date.')
            ]
        ])->add('validUntil', 'datetime', [
            'label' => $this->__('Valid until'),
            'widget' => 'choice',
            'required' => false,
            'attr' => [
                'help' => $this->__('If you specify a date, the permission rule will only be taken into account until the specified date.')
            ]
        ]);
    }
}
