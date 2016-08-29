<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ImportType extends SymfonyAbstractType
{
    /**
     * @var FormTypeInterface
     */
    private $importerForm;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(FormTypeInterface $importerForm, TranslatorInterface $translator, SecurityManager $securityManager)
    {
        $this->importerForm = $importerForm;
        $this->translator = $translator;
        $this->securityManager = $securityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('collection', 'entity', [
                'required' => true,
                'class' => 'CmfcmfMediaModule:Collection\CollectionEntity',
                'query_builder' => function (EntityRepository $er) {
                    /** @var CollectionRepository $qb */
                    $qb = $this->securityManager->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS);
                    $qb
                        ->orderBy('c.root', 'ASC')
                        ->addOrderBy('c.lft', 'ASC')
                        ->andWhere($qb->expr()->not($qb->expr()->eq('c.id', ':uploadCollectionId')))
                        ->setParameter('uploadCollectionId', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID);

                    return $qb;
                },
                'placeholder' => $this->translator->trans('Select collection', [], 'cmfcmfmediamodule'),
                'property' => 'indentedTitle',
            ])
            ->add('importSettings', $this->importerForm)
            ->add('import', 'submit', [
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'cmfcmfmediamodule_settingstype';
    }
}
