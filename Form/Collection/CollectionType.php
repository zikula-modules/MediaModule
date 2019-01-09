<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Collection;

use Cmfcmf\Module\MediaModule\CollectionTemplate\TemplateCollection;
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionCategoryAssignmentEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\Repository\MediaRepository;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\CollectionTemplate\TemplateType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class CollectionType extends AbstractType
{
    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @param TranslatorInterface  $translator
     * @param TemplateCollection   $templateCollection
     * @param SecurityManager      $securityManager
     * @param VariableApiInterface $variableApi
     */
    public function __construct(
        TranslatorInterface $translator,
        TemplateCollection $templateCollection,
        SecurityManager $securityManager,
        VariableApiInterface $variableApi
    ) {
        $this->translator = $translator;
        $this->templateCollection = $templateCollection;
        $this->securityManager = $securityManager;
        $this->variableApi = $variableApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $escapingStrategy = $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection');
        switch ($escapingStrategy) {
            case 'raw':
                $descriptionHelp = $this->translator->trans('You may use HTML.', [], 'cmfcmfmediamodule');
                break;
            case 'text':
                $descriptionHelp = $this->translator->trans('Only plaintext allowed.', [], 'cmfcmfmediamodule');
                break;
            case 'markdown':
                $descriptionHelp = $this->translator->trans('You may use MarkDown.', [], 'cmfcmfmediamodule');
                break;
            default:
                throw new \LogicException();
        }

        /** @var CollectionEntity $theCollection */
        $theCollection = $options['data'];
        $securityManager = $this->securityManager;

        $builder->add('title', TextType::class, [
            'label' => $this->translator->trans('Title', [], 'cmfcmfmediamodule')
        ]);
        /**
        If enabled, breaks slug generation of children when the slug is changed.
        if ($this->variableApi->get('CmfcmfMediaModule', 'slugEditable')) {
            $builder->add('slug', TextType::class, [
                'label' => $this->translator->trans('Slug', [], 'cmfcmfmediamodule'),
                'required'=> false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Leave empty to autogenerate', [], 'cmfcmfmediamodule')
                ]
            ]);
        }*/

        $builder
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description', [], 'cmfcmfmediamodule'),
                'required' => false,
                'attr' => [
                    'help' => $descriptionHelp
                ]
            ])
            ->add('categoryAssignments', CategoriesType::class, [
                'label' => $this->translator->trans('Categories', [], 'cmfcmfmediamodule'),
                'required' => false,
                'multiple' => true,
                'module' => 'CmfcmfMediaModule',
                'entity' => 'CollectionEntity',
                'entityCategoryClass' => CollectionCategoryAssignmentEntity::class,
            ])
            ->add('defaultTemplate', TemplateType::class, [
                'label' => $this->translator->trans('Display', [], 'cmfcmfmediamodule'),
            ])
            ->add('parent', EntityType::class, [
                'class' => CollectionEntity::class,
                'required' => false,
                'label' => $this->translator->trans('Parent', [], 'cmfcmfmediamodule'),
                'placeholder' => $this->translator->trans('No parent', [], 'cmfcmfmediamodule'),
                'query_builder' => function (EntityRepository $er) use (
                    $theCollection,
                    $securityManager
                ) {
                    /** @var CollectionRepository $er */
                    $qb = $securityManager->getCollectionsWithAccessQueryBuilder(
                        CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS
                    );
                    $qb->orderBy('c.root', 'ASC')
                        ->addOrderBy('c.lft', 'ASC');
                    if (null != $theCollection->getId()) {
                        // The collection is currently edited. Make sure it's not placed into
                        // itself or one of it's children.
                        $childrenQuery = $er->getChildrenQuery($theCollection);
                        $qb
                            ->andWhere(
                                $qb->expr()->notIn(
                                    'c.id',
                                    $childrenQuery->getDQL()
                                )
                            )
                            ->andWhere($qb->expr()->neq('c.id', ':id'))
                            ->setParameter('id', $theCollection->getId())
                        ;
                        $childrenQuery->getParameters()->forAll(function ($key, Parameter $parameter) use ($qb) {
                            $qb->setParameter($parameter->getName(), $parameter->getValue());
                        });
                    }

                    return $qb;
                },
                'data' => $options['parent'],
                'choice_label' => 'indentedTitle',
            ])
            ->add('watermark', EntityType::class, [
                'class' => AbstractWatermarkEntity::class,
                'required' => false,
                'label' => $this->translator->trans('Watermark', [], 'cmfcmfmediamodule'),
                'data' => null !== $theCollection->getId() ? $theCollection->getWatermark() :
                    (isset($options['parent']) ? $options['parent']->getWatermark() : null),
                'placeholder' => $this->translator->trans('No watermark', [], 'cmfcmfmediamodule'),
                'choice_label' => 'title',
            ])
            ->add('primaryMedium', EntityType::class, [
                'class' => AbstractMediaEntity::class,
                'required' => false,
                'label' => $this->translator->trans('Primary medium', [], 'cmfcmfmediamodule'),
                'placeholder' => $this->translator->trans('First medium of collection', [], 'cmfcmfmediamodule'),
                'disabled' => null === $theCollection->getId(),
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) use ($theCollection) {
                    /** @var MediaRepository $er */
                    $qb = $er->createQueryBuilder('m');
                    $qb->where($qb->expr()->eq('m.collection', ':collection'))
                        ->setParameter('collection', $theCollection->getId());

                    return $qb;
                },
                'attr' => [
                    'help' => $this->translator->trans('The primary medium is used as collection thumbnail. It must be part of the collection.', [], 'cmfcmfmediamodule')
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
                'parent' => null
            ])
        ;
    }
}
