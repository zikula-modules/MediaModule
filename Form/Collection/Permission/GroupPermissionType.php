<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\GroupsModule\Entity\GroupEntity;

class GroupPermissionType extends AbstractPermissionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $groupNames = $this->groupRepository->getGroupNamesById();

        $choices = [];
        $choices[-1] = $this->translator->trans('All groups', [], 'cmfcmfmediamodule');

        foreach ($groupNames as $groupId => $groupName) {
            $choices[$groupId] = $groupName;
        }

        $builder->add('groupIds', ChoiceType::class, [
            'label' => $this->translator->trans('Groups', [], 'cmfcmfmediamodule'),
            'multiple' => true,
            'choices' => $choices
        ]);

        parent::buildForm($builder, $options);
    }
}
