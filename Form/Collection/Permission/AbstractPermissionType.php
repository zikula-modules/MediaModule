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

use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\Type\PermissionLevelType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
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

    /**
     * @param TranslatorInterface      $translator
     * @param SecurityManager          $securityManager
     * @param GroupRepositoryInterface $groupRepository
     * @param UserRepositoryInterface  $userRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        SecurityManager $securityManager,
        GroupRepositoryInterface $groupRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->translator = $translator;
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
                'label' => $this->translator->trans('Permission level', [], 'cmfcmfmediamodule'),
                'permissionLevel' => $options['permissionLevel']
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

        if ($this->securityManager->hasPermission($options['collection'], CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)) {
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
