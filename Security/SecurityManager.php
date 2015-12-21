<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionContainer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\PermissionsModule\Api\PermissionApi;

class SecurityManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var SecurityTree
     */
    private $collectionSecurityGraph;

    /**
     * @var array
     */
    private $levels;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CollectionPermissionContainer
     */
    private $collectionPermissionContainer;

    public function __construct(
        TranslatorInterface $translator,
        PermissionApi $permissionApi,
        EntityManagerInterface $em,
        CollectionPermissionContainer $collectionPermissionContainer
    )
    {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->domain = 'cmfcmfmediamodule';
        $this->em = $em;
        $this->collectionPermissionContainer = $collectionPermissionContainer;

        $this->levels = [
            'view' => ACCESS_OVERVIEW,
            'display' => ACCESS_READ,
            'download' => ACCESS_COMMENT,
            'moderate' => ACCESS_MODERATE,
            'add' => ACCESS_ADD,
            'new' => ACCESS_ADD,
            'create' => ACCESS_ADD,
            'edit' => ACCESS_EDIT,
            'delete' => ACCESS_DELETE,
            'admin' => ACCESS_ADMIN
        ];
    }

    public function hasPermission($objectOrType, $action)
    {
        if (is_object($objectOrType)) {
            /** @var mixed $objectOrType */
            if ($objectOrType instanceof CollectionEntity) {
                return $this->hasCollectionPermission($objectOrType, $action);
            }
            if ($objectOrType instanceof AbstractMediaEntity) {
                return $this->hasCollectionPermission($objectOrType->getCollection(), $action);
            }
            $id = $objectOrType->getId();
            $class = get_class($objectOrType);
            $type = lcfirst(substr($class, strrpos($class, '/') + 1, -strlen('Entity')));
        } else {
            $id = "";
            $type = $objectOrType;
        }

        return $this->permissionApi->hasPermission("CmfcmfMediaModule:$type:", "$id::", $this->levels[$action]);
    }

    private function hasCollectionPermission(CollectionEntity $collection, $permLevel)
    {
        $collectionRepository = $this->em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');

        // @todo Check if it's the user's own collection.

        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from('CmfcmfMediaModule:Permission\AbstractPermissionEntity', 'p')
            ->leftJoin('p.collection', 'c')
            ->where(
                $qb->expr()->in(
                    'c.id',
                    $collectionRepository->getPathQueryBuilder($collection)->getDQL()
                )
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('p.validAfter'),
                    $qb->expr()->lte('p.validAfter', ':now')
                )
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('p.validUntil'),
                    $qb->expr()->lt(':now', 'p.validUntil')
                )
            )
            ->orderBy('p.position', 'ASC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults(1);

        $or = $qb->expr()->orX();
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        foreach ($collectionPermissions as $collectionPermission) {
            $or->add($collectionPermission->getApplicablePermissionsExpression($qb));
        }
        $qb->andWhere($or);

        /** @var AbstractPermissionEntity $permission */
        $permission = $qb->getQuery()->getSingleResult();
        $securityGraph = $this->getCollectionSecurityGraph();
        foreach ($permission->getPermissionLevels() as $permissionLevel) {
            if ($securityGraph->getChildrenOfVertex($securityGraph->getVertex($permissionLevel), SecurityTree::EDGE_TYPE_INCLUDED_PERMISSIONS)->hasVertexId($permLevel)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SecurityGraph
     */
    public function getCollectionSecurityGraph()
    {
        if (!$this->collectionSecurityGraph) {
            $this->collectionSecurityGraph = SecurityTree::createGraph($this->translator, $this->domain);
        }

        return $this->collectionSecurityGraph;
    }

    public function hasPermissionRaw($component, $instance, $level)
    {
        return $this->permissionApi->hasPermission($component, $instance, $level);
    }

    /**
     * @return SecurityCategory[]
     */
    public function getCollectionSecurityCategories()
    {
        return SecurityTree::getCategories($this->translator, $this->domain);
    }
}
