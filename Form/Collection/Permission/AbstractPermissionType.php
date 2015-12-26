<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection\Permission;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
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

    public function __construct(CollectionEntity $collectionEntity, SecurityManager $securityManager)
    {
        parent::__construct();

        $this->securityManager = $securityManager;
        $this->collectionEntity = $collectionEntity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('permissionLevels', 'cmfcmfmediamodule_permission', [
            'label' => $this->__('Permission level') // @todo Make sure to disallow the change permission level if appropriate.
        ])->add('description', 'textarea', [
            'label' => $this->__('Description'),
            'required' => false,
            'attr' => [
                'help' => $this->__('This is just for you to remember why you created this permission.')
            ]
        ])->add('appliedToSelf', 'checkbox', [
            'label' => $this->__('Applies to the collection itself'),
            'required' => false
        ])->add('appliedToSubCollections', 'checkbox', [
            'label' => $this->__('Applies to sub-collections'),
            'required' => false
        ]);

        if ($this->securityManager->hasPermission($this->collectionEntity, CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)) {
            $builder->add('goOn', 'checkbox', [
                'label' => $this->__('Go on if this permission is not sufficient'),
                'required' => false
            ]);
        } else {
            $builder->add('goOn', 'checkbox', [
                'label' => $this->__('Go on if this permission is not sufficient'),
                'required' => true,
                'data' => true,
                'attr' => [
                    'disabled' => true
                ],
            ]);
        }

        $builder->add('validAfter', 'datetime', [
            'label' => $this->__('Valid after'),
            'widget' => 'choice',
            'required' => false,
            'attr' => [
                'help' => $this->__('If you specify a date, the permission rule will only be taken into account after the specified date.')
            ]
        ])->add('validUntil', 'datetime', [
            'label' => $this->__('Valid until'),
            'widget' => 'choice',
            'required' => false,
            'attr' => [
                'help' => $this->__('If you specify a date, the permission rule will only be taken into account until the specified date.')
            ]
        ]);
    }
}
