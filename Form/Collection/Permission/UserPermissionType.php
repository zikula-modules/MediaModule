<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Symfony\Component\Form\FormBuilderInterface;

class UserPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = \UserUtil::getAll();
        $choices = array_map(function ($user) {
            return $user['uname'];
        }, $users);

        $builder->add('userIds', 'choice', [
            'label' => $this->__('Users'),
            'multiple' => true,
            'choices' => $choices
        ]);

        parent::buildForm($builder, $options);
    }
}
