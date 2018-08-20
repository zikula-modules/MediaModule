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
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class CollectionBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];
        /** @var SecurityManager $securityManager */
        $securityManager = $options['securityManager'];
        /** @var TemplateCollection $templateCollection */
        $templateCollection = $options['templateCollection'];
        /** @var CollectionRepository $collectionRepository */
        $collectionRepository = $options['collectionRepository'];

        $collectionOptions = [
            'required' => true,
            'label' => $translator->trans('Collection', [], 'cmfcmfmediamodule'),
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
            'placeholder' => $translator->trans('Select collection', [], 'cmfcmfmediamodule'),
            'choice_label' => 'indentedTitle',
            'multiple' => false
        ];
        $builder
            ->add('id', EntityType::class, $collectionOptions)
            ->add('showHooks', CheckboxType::class, [
                'label' => $translator->trans('Show hooks', [], 'cmfcmfmediamodule'),
                'required' => false,
                'disabled' => true
            ])
            ->add('template', ChoiceType::class, [
                'label' => $translator->trans('Template', [], 'cmfcmfmediamodule'),
                'required' => false,
                'placeholder' => $translator->trans('Default', [], 'cmfcmfmediamodule'),
                'choices' => $templateCollection->getCollectionTemplateTitles()
            ])
            ->add('showChildCollections', CheckboxType::class, [
                'label' => $translator->trans('Show child collections', [], 'cmfcmfmediamodule'),
                'required' => false
            ])
            ->add('showEditAndDownloadLinks', CheckboxType::class, [
                'label' => $translator->trans('Show edit and download links', [], 'cmfcmfmediamodule'),
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

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_collectionblock';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['translator', 'securityManager', 'templateCollection', 'collectionRepository']);
    }
}
