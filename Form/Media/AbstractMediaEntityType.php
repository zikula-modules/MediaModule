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

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\DataTransformer\ArrayToJsonTransformer;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Provides some convenience methods for all media form types.
 */
abstract class AbstractMediaEntityType extends AbstractType
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(
        SecurityManager $securityManager,
        VariableApiInterface $variableApi,
        EntityManagerInterface $em
    ) {
        $this->securityManager = $securityManager;
        $this->variableApi = $variableApi;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $editorClass = 'noeditor';
        $escapingStrategy = $this->variableApi->get(
            'CmfcmfMediaModule',
            'descriptionEscapingStrategyForMedia'
        );
        switch ($escapingStrategy) {
            case 'raw':
                $descriptionHelp = /** @Translate */ 'You may use HTML.';
                $editorClass = '';
                break;
            case 'text':
                $descriptionHelp = /** @Translate */ 'Only plaintext allowed.';
                break;
            case 'markdown':
                $descriptionHelp = /** @Translate */ 'You may use MarkDown.';
                break;
            default:
                throw new \LogicException();
        }

        $hiddenAttr = [
            'class' => 'd-none'
        ];

        $securityManager = $this->securityManager;
        $allowTemporaryUploadCollection = $options['allowTemporaryUploadCollection'] ?? false;
        $collectionOptions = [
            'required' => true,
            'label' => 'Collection',
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
            'placeholder' => 'Select collection',
            'choice_label' => 'indentedTitle',
        ];
        if (isset($options['parent']) && null !== $options['parent']) {
            $collectionOptions['data'] = $options['parent'];
        }

        $builder
            ->add('collection', EntityType::class, $collectionOptions)
            ->add('categoryAssignments', CategoriesType::class, [
                'label' => 'Categories',
                'required' => false,
                'multiple' => true,
                'module' => 'CmfcmfMediaModule',
                'entity' => 'AbstractMediaEntity',
                'entityCategoryClass' => MediaCategoryAssignmentEntity::class,
            ])
            ->add(
                'title',
                isset($options['hiddenFields']) && in_array(
                    'title',
                    $options['hiddenFields']
                ) ? HiddenType::class : TextType::class,
                [
                    'label' => 'Title'
                ]
            )
            ->add(
                'description',
                isset($options['hiddenFields']) && in_array(
                    'description',
                    $options['hiddenFields']
                ) ? HiddenType::class : TextareaType::class,
                [
                    'required' => false,
                    'label' => 'Description',
                    'attr' => [
                        'class' => $editorClass
                    ],
                    /** @Ignore */
                    'help' => $descriptionHelp
                ]
            )
        ;
        $licenseData = [
            'required' => false,
            'label' => 'License',
            'class' => LicenseEntity::class,
            'preferred_choices' => function (LicenseEntity $license) {
                return !$license->isOutdated();
            },
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('l')
                    ->orderBy('l.title', 'ASC')
                    ->where('l.enabledForUpload = 1');
            // @todo Move to the actual uploadable file types.
            },
            'placeholder' => 'Unknown',
            'choice_label' => 'title',
            'attr' => isset($options['hiddenFields']) && in_array(
                'license',
                $options['hiddenFields']
            ) ? $hiddenAttr : [],
            'label_attr' => isset($options['hiddenFields']) && in_array(
                'license',
                $options['hiddenFields']
            ) ? $hiddenAttr : []
        ];
        if (true === $options['isCreation']) {
            $defaultLicense = $this->variableApi->get('CmfcmfMediaModule', 'defaultLicense', null);
            if (null !== $defaultLicense) {
                $defaultLicense = $this->em->find('CmfcmfMediaModule:License\LicenseEntity', $defaultLicense);
            }
            $licenseData['data'] = $defaultLicense;
        }

        $builder->add('license', EntityType::class, $licenseData);

        $builder
            ->add(
                'author',
                isset($options['hiddenFields']) && in_array(
                    'author',
                    $options['hiddenFields']
                ) ? HiddenType::class : TextType::class,
                [
                    'label' => 'Author',
                    'required' => false,
                    'empty_data' => null
                ]
            )
            ->add(
                'authorUrl',
                isset($options['hiddenFields']) && in_array(
                    'authorUrl',
                    $options['hiddenFields']
                ) ? HiddenType::class : UrlType::class,
                [
                    'label' => 'Author URL',
                    'required' => false,
                    'empty_data' => null
                ]
            )
            ->add(
                'authorAvatarUrl',
                isset($options['hiddenFields']) && in_array(
                    'authorAvatarUrl',
                    $options['hiddenFields']
                ) ? HiddenType::class : UrlType::class,
                [
                    'label' => 'Author Avatar URL',
                    'required' => false,
                    'empty_data' => null
                ]
            )
            ->add('mediaType', HiddenType::class, [
                'mapped' => false
            ])
            ->add('extraData', HiddenType::class)
        ;

        $builder->get('extraData')->addModelTransformer(new ArrayToJsonTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'isCreation' => false,
                'parent' => null,
                'allowTemporaryUploadCollection' => false
            ])
            ->setAllowedTypes('isCreation', 'bool')
            ->setAllowedTypes('allowTemporaryUploadCollection', 'bool')
        ;
    }
}
