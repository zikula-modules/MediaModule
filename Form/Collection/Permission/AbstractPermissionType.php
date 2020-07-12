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

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

abstract class AbstractPermissionType extends AbstractType
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    public function __construct(
        SecurityManager $securityManager,
        GroupRepositoryInterface $groupRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->securityManager = $securityManager;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('permissionLevels', PermissionLevelType::class, [
                'label' => 'Permission level',
                'permissionLevel' => $options['permissionLevel']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'help' => 'This is just for you to remember why you created this permission.'
            ])
            ->add('appliedToSelf', CheckboxType::class, [
                'label' => 'Applies to the collection itself',
                'required' => false
            ])
            ->add('appliedToSubCollections', CheckboxType::class, [
                'label' => 'Applies to sub-collections',
                'required' => false
            ])
        ;

        if ($this->securityManager->hasPermission($options['collection'], CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)) {
            $builder->add('goOn', CheckboxType::class, [
                'label' => 'Go on if this permission is not sufficient',
                'required' => false
            ]);
        } else {
            $builder->add('goOn', CheckboxType::class, [
                'label' => 'Go on if this permission is not sufficient',
                'data' => true,
                'attr' => [
                    'disabled' => true
                ],
            ]);
        }

        $builder
            ->add('validAfter', DateTimeType::class, [
                'label' => 'Valid after',
                'widget' => 'choice',
                'required' => false,
                'help' => 'If you specify a date, the permission rule will only be taken into account after the specified date.'
            ])
            ->add('validUntil', DateTimeType::class, [
                'label' => 'Valid until',
                'widget' => 'choice',
                'required' => false,
                'help' => 'If you specify a date, the permission rule will only be taken into account until the specified date.'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'collection' => null,
                'permissionLevel' => null
            ])
        ;
    }
}
