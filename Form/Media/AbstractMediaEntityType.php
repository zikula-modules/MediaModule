<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\DataTransformer\ArrayToJsonTransformer;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Provides some convenience methods for all media form types.
 */
abstract class AbstractMediaEntityType extends AbstractType
{
    /**
     * @var bool
     */
    protected $isCreation;

    /**
     * @var CollectionEntity|null
     */
    private $parent;

    /**
     * @var bool
     */
    private $allowTemporaryUploadCollection;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(
        SecurityManager $securityManager,
        $isCreation = false,
        CollectionEntity $parent = null,
        $allowTemporaryUploadCollection = false
    ) {
        $this->securityManager = $securityManager;
        $this->isCreation = $isCreation;
        $this->parent = $parent;
        $this->allowTemporaryUploadCollection = $allowTemporaryUploadCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $escapingStrategy = \ModUtil::getVar(
            'CmfcmfMediaModule',
            'descriptionEscapingStrategyForMedia');
        switch ($escapingStrategy) {
            case 'raw':
                $descriptionHelp = $this->translator->trans(
                    'You may use HTML.',
                    [],
                    'cmfcmfmediamodule');
                break;
            case 'text':
                $descriptionHelp = $this->translator->trans(
                    'Only plaintext allowed.',
                    [],
                    'cmfcmfmediamodule');
                break;
            case 'markdown':
                $descriptionHelp = $this->translator->trans(
                    'You may use MarkDown.',
                    [],
                    'cmfcmfmediamodule');
                break;
            default:
                throw new \LogicException();
        }

        $hiddenAttr = [
            'class' => 'hidden',
        ];

        $securityManager = $this->securityManager;
        $allowTemporaryUploadCollection = $this->allowTemporaryUploadCollection;
        $collectionOptions = [
            'required' => true,
            'label' => $this->translator->trans('Collection', [], 'cmfcmfmediamodule'),
            'class' => 'CmfcmfMediaModule:Collection\CollectionEntity',
            'query_builder' => function (EntityRepository $er) use (
                $allowTemporaryUploadCollection,
                $securityManager
            ) {
                /** @var CollectionRepository $er */
                $qb = $securityManager->getCollectionsWithAccessQueryBuilder(
                    CollectionPermissionSecurityTree::PERM_LEVEL_ADD_MEDIA
                );
                $qb->orderBy('c.root', 'ASC')
                    ->addOrderBy('c.lft', 'ASC');

                if ($allowTemporaryUploadCollection) {
                    $qb->orWhere(
                        $qb->expr()->eq('c.id', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID)
                    );
                }

                return $qb;
            },
            'placeholder' => $this->translator->trans('Select collection', [], 'cmfcmfmediamodule'),
            'property' => 'indentedTitle',
        ];
        if ($this->parent !== null) {
            $collectionOptions['data'] = $this->parent;
        }

        $builder
            ->add('collection', 'entity', $collectionOptions)
            ->add('categoryAssignments', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
                'required' => false,
                'multiple' => true,
                'module' => 'CmfcmfMediaModule',
                'entity' => 'AbstractMediaEntity',
                'entityCategoryClass' => 'Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity',
            ])
            ->add(
                'title',
                isset($options['hiddenFields']) && in_array(
                    'title',
                    $options['hiddenFields']) ? 'hidden' : 'text',
                [
                    'label' => $this->translator->trans('Title', [], 'cmfcmfmediamodule')
                ])
            ->add(
                'description',
                isset($options['hiddenFields']) && in_array(
                    'description',
                    $options['hiddenFields']) ? 'hidden' : 'textarea',
                [
                    'required' => false,
                    'label' => $this->translator->trans('Description', [], 'cmfcmfmediamodule'),
                    'attr' => [
                        'help' => $descriptionHelp
                    ]
                ])
            ->add(
                'license',
                'entity',
                [
                    'required' => false,
                    'label' => $this->translator->trans('License', [], 'cmfcmfmediamodule'),
                    'class' => 'CmfcmfMediaModule:License\LicenseEntity',
                    'preferred_choices' => function (LicenseEntity $license) {
                        return !$license->isOutdated();
                    },
                    'data' => \ModUtil::getVar('CmfcmfMediaModule', 'defaultLicense'),
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.title', 'ASC')
                            ->where('l.enabledForUpload = 1');
                        // @todo Move to the actual uploadable file types.
                    },
                    'placeholder' => $this->translator->trans('Unknown', [], 'cmfcmfmediamodule'),
                    'property' => 'title',
                    'attr' => isset($options['hiddenFields']) && in_array(
                        'license',
                        $options['hiddenFields']) ? $hiddenAttr : [],
                    'label_attr' => isset($options['hiddenFields']) && in_array(
                        'license',
                        $options['hiddenFields']) ? $hiddenAttr : []
                ])
            ->add(
                'author',
                isset($options['hiddenFields']) && in_array(
                    'author',
                    $options['hiddenFields']) ? 'hidden' : 'text',
                [
                    'label' => $this->translator->trans('Author', [], 'cmfcmfmediamodule'),
                    'required' => false,
                    'empty_data' => null
                ])
            ->add(
                'authorUrl',
                isset($options['hiddenFields']) && in_array(
                    'authorUrl',
                    $options['hiddenFields']) ? 'hidden' : 'url',
                [
                    'label' => $this->translator->trans('Author URL', [], 'cmfcmfmediamodule'),
                    'required' => false,
                    'empty_data' => null
                ])
            ->add(
                'authorAvatarUrl',
                isset($options['hiddenFields']) && in_array(
                    'authorAvatarUrl',
                    $options['hiddenFields']) ? 'hidden' : 'url',
                [
                    'label' => $this->translator->trans(
                        'Author Avatar URL',
                        [],
                        'cmfcmfmediamodule'),
                    'required' => false,
                    'empty_data' => null
                ])
            ->add(
                'mediaType',
                'hidden',
                [
                    'mapped' => false
                ])
            ->add('extraData', 'hidden');

        $builder->get('extraData')->addModelTransformer(new ArrayToJsonTransformer());
    }
}
