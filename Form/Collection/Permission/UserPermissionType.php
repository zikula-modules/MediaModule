<?php

declare(strict_types=1);

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
use Translation\Extractor\Annotation\Ignore;

class UserPermissionType extends AbstractPermissionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = $this->userRepository->findAll();
        $choices = [];
        foreach ($users as $user) {
            $choices[$user['uname']] = $user['uid'];
        }

        $builder->add('userIds', ChoiceType::class, [
            'label' => 'Users',
            'multiple' => true,
            'choices' => /** @Ignore */ $choices
        ]);

        parent::buildForm($builder, $options);
    }
}
