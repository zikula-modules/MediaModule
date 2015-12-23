<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Symfony\Component\Form\FormBuilderInterface;

class PasswordPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('password', 'password', [
            'label' => $this->__('Password'),
        ]);
    }
}
