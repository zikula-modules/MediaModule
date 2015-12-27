<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Symfony\Component\Form\FormBuilderInterface;
use Zikula\GroupsModule\Entity\GroupEntity;

class GroupPermissionType extends AbstractPermissionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var GroupEntity[]|false $groups */
        $groups = \ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        if ($groups === false) {
            $groups = [];
        }

        $choices = [];
        $choices[-1] = $this->__('All groups');

        foreach ($groups as $group) {
            $choices[$group->getGid()] = $group->getName();
        }

        $builder->add('groupIds', 'choice', [
            'label' => $this->__('Groups'),
            'multiple' => true,
            'choices' => $choices
        ]);

        parent::buildForm($builder, $options);
    }
}
