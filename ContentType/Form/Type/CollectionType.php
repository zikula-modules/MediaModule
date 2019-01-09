<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\ContentType\Form\Type;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Form\CollectionTemplate\TemplateType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Common\Content\AbstractContentFormType;

/**
 * Collection content type form type.
 */
class CollectionType extends AbstractContentFormType
{
    /**
     * @var CollectionRepository
     */
    private $collectionRepository;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @param TranslatorInterface    $translator
     * @param SecurityManager        $securityManager
     * @param EntityManagerInterface $em
     */
    public function __construct(
        TranslatorInterface $translator,
        SecurityManager $securityManager,
        EntityManagerInterface $em
    ) {
        $this->setTranslator($translator);
        $this->securityManager = $securityManager;
        $this->collectionRepository = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityManager = $this->securityManager;
        $collectionRepository = $this->collectionRepository;

        $collectionOptions = [
            'required' => true,
            'label' => $this->__('Collection', 'cmfcmfmediamodule'),
            'class' => CollectionEntity::class,
            'query_builder' => function (EntityRepository $er) use ($securityManager) {
                /** @var CollectionRepository $er */
                $qb = $securityManager->getCollectionsWithAccessQueryBuilder(
                    CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW
                );
                $qb->orderBy('c.root', 'ASC')
                    ->addOrderBy('c.lft', 'ASC');

                return $qb;
            },
            'placeholder' => $this->__('Select collection', 'cmfcmfmediamodule'),
            'choice_label' => 'indentedTitle',
            'multiple' => false
        ];
        $builder
            ->add('id', EntityType::class, $collectionOptions)
            ->add('template', TemplateType::class, [
                'label' => $this->__('Display', 'cmfcmfmediamodule'),
            ])
            ->add('showChildCollections', CheckboxType::class, [
                'label' => $this->__('Show child collections', 'cmfcmfmediamodule'),
                'required' => false
            ])
            ->add('showEditAndDownloadLinks', CheckboxType::class, [
                'label' => $this->__('Show edit and download links', 'cmfcmfmediamodule'),
                'required' => false
            ])
            ->addModelTransformer(new CallbackTransformer(
                function ($data) use ($collectionRepository) {
                    $data['id'] = isset($data['id']) ? $collectionRepository->findOneBy(['id' => $data['id']]) : null;

                    return $data;
                },
                function ($data) {
                    $data['id'] = isset($data['id']) ? $data['id']->getId() : null;

                    return $data;
                }
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_contenttype_collection';
    }
}
