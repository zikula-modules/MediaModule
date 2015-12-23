<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionContainer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
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
        $securityGraph = $this->getCollectionSecurityGraph();
        $collectionRepository = $this->em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');

        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'p')
            ->leftJoin('p.collection', 'c')
            ->where(
                // Get all permissions of the collection itself + all it's parent collections.
                $qb->expr()->in(
                    'c.id',
                    // Retrieve the ids of all parent collections + the collection itself.
                    $collectionRepository->getPathQueryBuilder($collection)->getDQL()
                )
            )
            ->andWhere(
                // Make sure the permission is valid already.
                $qb->expr()->orX(
                    $qb->expr()->isNull('p.validAfter'),
                    $qb->expr()->lte('p.validAfter', ':now')
                )
            )
            ->andWhere(
                // Make sure the permission isn't invalid already.
                $qb->expr()->orX(
                    $qb->expr()->isNull('p.validUntil'),
                    $qb->expr()->lt(':now', 'p.validUntil')
                )
            )
            ->andWhere(
                // Make sure the permission applies.
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        // For permissions of the collection itself, the "appliesToSelf" flag must be set.
                        $qb->expr()->eq('c.id', ':collectionId'),
                        $qb->expr()->eq('p.appliedToSelf', true)
                    ),
                    $qb->expr()->andX(
                        // For parent collections' permissions, the "appliedToSubCollections" flag must be set.
                        $qb->expr()->neq('c.id', ':collectionId'),
                        $qb->expr()->eq('p.appliedToSubCollections', true)
                    )
                )
            )
            ->orderBy('p.position', 'ASC')
            ->setParameter('now', new \DateTime())
            ->setParameter('collectionId', $collection->getId())
        ;

        // Now filter all permissions which actually belong to the current user.
        $or = $qb->expr()->orX();
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        foreach ($collectionPermissions as $collectionPermission) {
            $or->add($collectionPermission->getApplicablePermissionsExpression($qb));
        }
        $qb->andWhere($or);

        /** @var AbstractPermissionEntity[] $permissionEntities */
        $permissionEntities = $qb->getQuery()->execute();

        //$wouldHaveAccess = false;
        foreach ($permissionEntities as $permissionEntity) {
            foreach ($permissionEntity->getPermissionLevels() as $permissionLevel) {
                if ($securityGraph->getChildrenOfVertex($securityGraph->getVertex($permissionLevel))->hasVertexId($permLevel)) {
                    // The permissionlevel is sufficient. Now check for permission restrictions.
                    //$restrictionsOk = true;
                    // @todo Implement these.
                    //foreach ($permissionEntity->getRestrictions() as $restriction) {
                    //    if (!$restriction->ok()) {
                    //        $restrictionsOk = false;
                    //        break;
                    //    }
                    //}
                    //if ($restrictionsOk) {
                        return true;
                    //} else {
                    //    $wouldHaveAccess = true;
                    //}
                }
            }
            // If we reach this point, no level of the currently inspected permission matched the required level.
            if (!$permissionEntity->isGoOn()) {
                break;
            }
        }

        //if ($wouldHaveAccess) {
        //    // Hey user, please enter your password!
        //}

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
