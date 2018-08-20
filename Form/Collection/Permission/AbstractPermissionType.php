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

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractPermissionType extends AbstractType
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var CollectionEntity
     */
    private $collectionEntity;

    /**
     * @var ?
     */
    private $permissionLevel;

    /**
     * @param CollectionEntity $collectionEntity
     * @param SecurityManager  $securityManager
     * @param ?                $permissionLevel
     */
    public function __construct(
        CollectionEntity $collectionEntity,
        SecurityManager $securityManager,
        $permissionLevel
    ) {
        $this->securityManager = $securityManager;
        $this->collectionEntity = $collectionEntity;
        $this->permissionLevel = $permissionLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('permissionLevels', PermissionLevelType::class, [
                'label' => $this->translator->trans('Permission level', [], 'cmfcmfmediamodule'),
                'permissionLevel' => $this->permissionLevel
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans(
                        'This is just for you to remember why you created this permission.',
                        [],
                        'cmfcmfmediamodule')
                ]
            ])
            ->add('appliedToSelf', CheckboxType::class, [
                'label' => $this->translator->trans('Applies to the collection itself', [], 'cmfcmfmediamodule'),
                'required' => false
            ])
            ->add('appliedToSubCollections', CheckboxType::class, [
                'label' => $this->translator->trans('Applies to sub-collections', [], 'cmfcmfmediamodule'),
                'required' => false
            ])
        ;

        if ($this->securityManager->hasPermission(
            $this->collectionEntity,
            CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)
        ) {
            $builder->add('goOn', CheckboxType::class, [
                'label' => $this->translator->trans('Go on if this permission is not sufficient', [], 'cmfcmfmediamodule'),
                'required' => false
            ]);
        } else {
            $builder->add('goOn', CheckboxType::class, [
                'label' => $this->translator->trans('Go on if this permission is not sufficient', [], 'cmfcmfmediamodule'),
                'data' => true,
                'attr' => [
                    'disabled' => true
                ],
            ]);
        }

        $builder
            ->add('validAfter', DateTimeType::class, [
                'label' => $this->translator->trans('Valid after', [], 'cmfcmfmediamodule'),
                'widget' => 'choice',
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans(
                        'If you specify a date, the permission rule will only be taken into account after the specified date.',
                        [],
                        'cmfcmfmediamodule')
                ]
            ])
            ->add('validUntil', DateTimeType::class, [
                'label' => $this->translator->trans('Valid until', [], 'cmfcmfmediamodule'),
                'widget' => 'choice',
                'required' => false,
                'attr' => [
                    'help' => $this->translator->trans(
                        'If you specify a date, the permission rule will only be taken into account until the specified date.',
                        [],
                        'cmfcmfmediamodule')
                ]
            ])
        ;
    }
}
