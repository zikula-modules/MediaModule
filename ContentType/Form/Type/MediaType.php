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

namespace Cmfcmf\Module\MediaModule\ContentType\Form\Type;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\Repository\MediaRepository;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\ExtensionsModule\ModuleInterface\Content\Form\Type\AbstractContentFormType;

/**
 * Media content type form type.
 */
class MediaType extends AbstractContentFormType
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(
        SecurityManager $securityManager,
        EntityManagerInterface $em
    ) {
        $this->securityManager = $securityManager;
        $this->mediaRepository = $em->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityManager = $this->securityManager;
        $mediaRepository = $this->mediaRepository;

        $mediumOptions = [
            'required' => true,
            'label' => 'Medium',
            'class' => AbstractMediaEntity::class,
            'query_builder' => function (EntityRepository $er) use ($securityManager) {
                /** @var MediaRepository $er */
                $qb = $securityManager->getMediaWithAccessQueryBuilder(
                    CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS
                );
                $qb->orderBy('m.title');

                return $qb;
            },
            'placeholder' => 'Select medium',
            'choice_label' => 'title',
            'multiple' => false
        ];
        $builder
            ->add('id', EntityType::class, $mediumOptions)
            ->addModelTransformer(new CallbackTransformer(
                function ($data) use ($mediaRepository) {
                    $data['id'] = isset($data['id']) ? $mediaRepository->findOneBy(['id' => $data['id']]) : null;

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
        return 'cmfcmfmediamodule_contenttype_media';
    }
}
