<?php

namespace Cmfcmf\Module\MediaModule\Form\Permission;

use Symfony\Component\Form\FormBuilderInterface;

class GroupPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('groupIds', 'choice', [
            'label' => $this->__('Groups'),
            'multiple' => true,
            'choices' => [
                '-1' => 'all groups',
                '1' => 'one group',
                '2' => 'another group'
            ]
        ]);
    }
}