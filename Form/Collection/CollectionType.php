<?php

namespace Cmfcmf\Module\MediaModule\Form\Collection;

use Cmfcmf\Module\MediaModule\CollectionTemplate\TemplateCollection;
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;

class CollectionType extends AbstractType
{
    /**
     * @var CollectionEntity
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

    public function __construct(TemplateCollection $templateCollection, CollectionEntity $parent, SecurityManager $securityManager)
    {
        parent::__construct();

        $this->parent = $parent;
        $this->templateCollection = $templateCollection;
        $this->securityManager = $securityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $escapingStrategy = \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection');
        switch ($escapingStrategy) {
            case 'raw':
                $descriptionHelp = $this->__('You may use HTML.');
                break;
            case 'text':
                $descriptionHelp = $this->__('Only plaintext allowed.');
                break;
            case 'markdown':
                $descriptionHelp = $this->__('You may use MarkDown.');
                break;
            default:
                throw new \LogicException();
        }

        /** @var CollectionEntity $theCollection */
        $theCollection = $options['data'];

        $builder
            ->add('title', 'text', [
                'label' => $this->__('Title')
            ])
        ;
        // If enabled, breaks slug generation of children when the slug is changed.
        //if (\ModUtil::getVar('CmfcmfMediaModule', 'slugEditable')) {
        //    $builder
        //        ->add('slug', 'text', [
        //            'label' => $this->__('Slug'),
        //            'required'=> false,
        //            'attr' => [
        //                'placeholder' => $this->__('Leave empty to autogenerate')
        //            ]
        //        ])
        //    ;
        //}
        $securityManager = $this->securityManager;

        $builder
            ->add('description', 'textarea', [
                'label' => $this->__('Description'),
                'required' => false,
                'attr' => [
                    'help' => $descriptionHelp
                ]
            ])
            ->add('defaultTemplate', 'choice', [
                'label' => $this->__('Template'),
                'required' => false,
                'placeholder' => $this->__('Default'),
                'choices' => $this->templateCollection->getCollectionTemplateTitles()
            ])
            ->add('parent', 'entity', [
                'class' => 'Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity',
                'required' => true,
                'label' => $this->__('Parent'),
                'query_builder' => function (EntityRepository $er) use ($theCollection, $securityManager) {
                    $qb = $securityManager->getCollectionsWithAccessQueryBuilder(
                        $theCollection->getId() != null ? CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION : CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS
                    );
                    $qb
                        ->orderBy('c.root', 'ASC')
                        ->addOrderBy('c.lft', 'ASC')
                    ;
                    // @todo Permissions!!
                    if ($theCollection->getId() != null) {
                        // The collection is currently edited.
                        $qb
                            ->andWhere($qb->expr()->neq('c.id', ':id'))
                            ->setParameter('id', $theCollection->getId())
                        ;
                    }

                    return $qb;
                },
                'data' => $this->parent,
                'property' => 'indentedTitle',
            ])
            ->add('watermark', 'entity', [
                'class' => 'CmfcmfMediaModule:Watermark\AbstractWatermarkEntity',
                'required' => false,
                'label' => $this->__('Watermark'),
                'data' => $theCollection->getId() !== null ? $theCollection->getWatermark() :
                    (isset($this->parent) ? $this->parent->getWatermark() : null),
                'placeholder' => $this->__('No watermark'),
                'property' => 'title',
            ])
        ;
    }
}
