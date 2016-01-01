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
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;

class CollectionType extends AbstractType
{
    /**
     * @var CollectionEntity|null
     */
    private $parent;

    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(
        TemplateCollection $templateCollection,
        CollectionEntity $parent = null,
        SecurityManager $securityManager
    ) {
        $this->parent = $parent;
        $this->templateCollection = $templateCollection;
        $this->securityManager = $securityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $escapingStrategy = \ModUtil::getVar(
            'CmfcmfMediaModule',
            'descriptionEscapingStrategyForCollection');
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

        $builder
            ->add(
                'title',
                'text',
                [
                    'label' => $this->translator->trans('Title', [], 'cmfcmfmediamodule')
                ]);
        // If enabled, breaks slug generation of children when the slug is changed.
        //if (\ModUtil::getVar('CmfcmfMediaModule', 'slugEditable')) {
        //    $builder
        //        ->add('slug', 'text', [
        //            'label' => $this->translator->trans('Slug', [], 'cmfcmfmediamodule'),
        //            'required'=> false,
        //            'attr' => [
        //                'placeholder' => $this->translator->trans('Leave empty to autogenerate', [], 'cmfcmfmediamodule')
        //            ]
        //        ])
        //    ;
        //}
        $securityManager = $this->securityManager;

        $builder
            ->add(
                'description',
                'textarea',
                [
                    'label' => $this->translator->trans('Description', [], 'cmfcmfmediamodule'),
                    'required' => false,
                    'attr' => [
                        'help' => $descriptionHelp
                    ]
                ])
            ->add(
                'defaultTemplate',
                'choice',
                [
                    'label' => $this->translator->trans('Template', [], 'cmfcmfmediamodule'),
                    'required' => false,
                    'placeholder' => $this->translator->trans('Default', [], 'cmfcmfmediamodule'),
                    'choices' => $this->templateCollection->getCollectionTemplateTitles()
                ]);
        if ($this->parent !== null) {
            $builder->add(
                'parent',
                'entity',
                [
                    'class' => 'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity',
                    'required' => true,
                    'label' => $this->translator->trans('Parent', [], 'cmfcmfmediamodule'),
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
                        if ($theCollection->getId() != null) {
                            // The collection is currently edited. Make sure it's not placed onto
                            // itself mor one of it's children.
                            $qb->andWhere(
                                $qb->expr()->not(
                                    $qb->expr()->in(
                                        'c.id',
                                        $er->getChildrenQuery($theCollection)->getDQL()
                                    )
                                )
                            )->andWhere($qb->expr()->neq('c.id', ':id'))
                                ->setParameter('id', $theCollection->getId());
                        }

                        return $qb;
                    },
                    'data' => $this->parent,
                    'property' => 'indentedTitle',
                ]);
        } else {
            $builder->add(
                'parent',
                'entity',
                [
                    'class' => 'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity',
                    'required' => false,
                    'label' => $this->translator->trans('Parent', [], 'cmfcmfmediamodule'),
                    'data' => $this->parent,
                    'placeholder' => $this->translator->trans('No parent', [], 'cmfcmfmediamodule'),
                    'choices' => [],
                    'attr' => [
                        'readonly' => true
                    ]
                ]);
        }
        $builder->add(
            'watermark',
            'entity',
            [
                'class' => 'CmfcmfMediaModule:Watermark\AbstractWatermarkEntity',
                'required' => false,
                'label' => $this->translator->trans('Watermark', [], 'cmfcmfmediamodule'),
                'data' => $theCollection->getId() !== null ? $theCollection->getWatermark() :
                    (isset($this->parent) ? $this->parent->getWatermark() : null),
                'placeholder' => $this->translator->trans('No watermark', [], 'cmfcmfmediamodule'),
                'property' => 'title',
            ]);
    }
}
