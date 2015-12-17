<?php

namespace Cmfcmf\Module\MediaModule\Form\Permission;

use Symfony\Component\Form\FormBuilderInterface;

class UserPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('userIds', 'choice', [
            'label' => $this->__('Users'),
            'multiple' => true,
            'choices' => [
                '1' => 'Bob',
                '2' => 'Alice'
            ]
        ]);
    }
}
