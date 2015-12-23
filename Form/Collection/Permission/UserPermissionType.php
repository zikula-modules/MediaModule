<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Symfony\Component\Form\FormBuilderInterface;

class UserPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $users = \UserUtil::getAll();
        $choices = array_map(function ($user) {
            return $user['uname'];
        }, $users);

        $builder->add('userIds', 'choice', [
            'label' => $this->__('Users'),
            'multiple' => true,
            'choices' => $choices
        ]);
    }
}
