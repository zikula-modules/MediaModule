<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionContainer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Do a plain, old security check.
     *
     * @param $component
     * @param $instance
     * @param $level
     * @return bool
     */
    public function hasPermissionRaw($component, $instance, $level)
    {
        return $this->permissionApi->hasPermission($component, $instance, $level);
    }

    /**
     * Check whether the current user has $permLevel access to the specified collection.
     *
     * @param CollectionEntity $collection
     * @param                  $permLevel
     * @return bool
     */
    private function hasCollectionPermission(CollectionEntity $collection, $permLevel)
    {
        static $cachedResult = [];

        if (!isset($cachedResult[$permLevel])) {
            $qb = $this->getCollectionsWithAccessQueryBuilder($permLevel);
            $cachedResult[$permLevel] = $qb->getQuery()->getArrayResult();
            $cachedResult[$permLevel] = array_column($cachedResult[$permLevel], 'id');
        }

        return in_array($collection->getId(), $cachedResult[$permLevel]);
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

    /**
     * Return all collections which the current user has $permLevel access to.
     *
     * @param $requestedLevel
     * @return QueryBuilder
     *
     * Select all collections where there is at least on permission which
     *     1. is associated to the collection itself or a parent collection.
     *     2. includes the required permission level or one of it's parent permission levels
     *     3. is currently valid (validAfter <= now < validUntil)
     *     4. has appliedToSelf = 1 and is associated to the collection itself.
     *          OR
     *        has appliedToSubCollections = 1 and is associated to a parent collection.
     *     5. has a position which is lower or equal to the position of the first permission having goOn = 0
     *        which applies to the collection.
     */
    public function getCollectionsWithAccessQueryBuilder($requestedLevel)
    {
        $securityGraph = $this->getCollectionSecurityGraph();

        $now = new \DateTime();

        $sufficientLevels = array_keys($securityGraph->getParentsOfVertex($securityGraph->getVertex($requestedLevel))->getMap());
        $sufficientLevels[] = $requestedLevel;

        // Get all the permissions which do apply.
        $okPermissionsQB = $this->em->createQueryBuilder();
        $okPermissionsQB->select($okPermissionsQB->expr()->min('p.position'))
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'p')
            ->leftJoin('p.collection', 'collectionOfPermission')
            ->where(
            // Get all permissions of the collection itself + all it's parent collections.
                $okPermissionsQB->expr()->in(
                    'collectionOfPermission.id',
                    // Retrieve the ids of all parent collections + the collection itself.
                    $this->getParentCollectionsQB('parentCollectionsOfcollectionOfPermission')->getDQL()
                )
            )
            ->andWhere(
            // Make sure the permission is valid already.
                $okPermissionsQB->expr()->orX(
                    $okPermissionsQB->expr()->isNull('p.validAfter'),
                    $okPermissionsQB->expr()->lte('p.validAfter', ':now')
                )
            )
            ->andWhere(
            // Make sure the permission isn't invalid already.
                $okPermissionsQB->expr()->orX(
                    $okPermissionsQB->expr()->isNull('p.validUntil'),
                    $okPermissionsQB->expr()->gt('p.validUntil', ':now')
                )
            )
            ->andWhere(
            // Make sure the permission applies.
                $okPermissionsQB->expr()->orX(
                    $okPermissionsQB->expr()->andX(
                    // For permissions of the collection itself, the "appliesToSelf" flag must be set.
                        $okPermissionsQB->expr()->eq('collectionOfPermission.id', 'c.id'),
                        $okPermissionsQB->expr()->eq('p.appliedToSelf', true)
                    ),
                    $okPermissionsQB->expr()->andX(
                    // For parent collections' permissions, the "appliedToSubCollections" flag must be set.
                        $okPermissionsQB->expr()->neq('collectionOfPermission.id', 'c.id'),
                        $okPermissionsQB->expr()->eq('p.appliedToSubCollections', true)
                    )
                )
            )
            ->andWhere($okPermissionsQB->expr()->eq('p.goOn', $okPermissionsQB->expr()->literal(false)))
            ->orderBy('p.position', 'ASC')
            ->setParameter('now', $now)
        ;
        // Filter the permissions, so that only the ones which belong to the current user are returned.
        $or = $okPermissionsQB->expr()->orX();
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        foreach ($collectionPermissions as $collectionPermission) {
            $or->add($collectionPermission->getApplicablePermissionsExpression($okPermissionsQB, 'p'));
        }
        $okPermissionsQB->andWhere($or);

        // Get all the permissions which do apply.
        $okPermissionsQB2 = $this->em->createQueryBuilder();
        $okPermissionsQB2->select($okPermissionsQB2->expr()->min('p2.position'))
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'p2')
            ->leftJoin('p2.collection', 'collectionOfPermission2')
            ->where(
            // Get all permissions of the collection itself + all it's parent collections.
                $okPermissionsQB2->expr()->in(
                    'collectionOfPermission2.id',
                    // Retrieve the ids of all parent collections + the collection itself.
                    $this->getParentCollectionsQB('parentCollectionsOfcollectionOfPermission2')->getDQL()
                )
            )
            ->andWhere(
            // Make sure the permission is valid already.
                $okPermissionsQB2->expr()->orX(
                    $okPermissionsQB2->expr()->isNull('p2.validAfter'),
                    $okPermissionsQB2->expr()->lte('p2.validAfter', ':now')
                )
            )
            ->andWhere(
            // Make sure the permission isn't invalid already.
                $okPermissionsQB2->expr()->orX(
                    $okPermissionsQB2->expr()->isNull('p2.validUntil'),
                    $okPermissionsQB2->expr()->gt('p2.validUntil', ':now')
                )
            )
            ->andWhere(
            // Make sure the permission applies.
                $okPermissionsQB2->expr()->orX(
                    $okPermissionsQB2->expr()->andX(
                    // For permissions of the collection itself, the "appliesToSelf" flag must be set.
                        $okPermissionsQB2->expr()->eq('collectionOfPermission2.id', 'c.id'),
                        $okPermissionsQB2->expr()->eq('p2.appliedToSelf', true)
                    ),
                    $okPermissionsQB2->expr()->andX(
                    // For parent collections' permissions, the "appliedToSubCollections" flag must be set.
                        $okPermissionsQB2->expr()->neq('collectionOfPermission2.id', 'c.id'),
                        $okPermissionsQB2->expr()->eq('p2.appliedToSubCollections', true)
                    )
                )
            )
            ->orderBy('p2.position', 'ASC')
            ->setParameter('now', $now)
        ;
        // Filter the permissions, so that only the ones which belong to the current user are returned.
        $or = $okPermissionsQB2->expr()->orX();
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        foreach ($collectionPermissions as $collectionPermission) {
            $or->add($collectionPermission->getApplicablePermissionsExpression($okPermissionsQB2, 'p2'));
        }
        $okPermissionsQB2->andWhere($or);

        $anotherQB = $this->em->createQueryBuilder();
        $anotherQB->select('x')
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'x')
            ->where($anotherQB->expr()->gte('x.position', '(' . $okPermissionsQB2->getDQL() . ')'))
            ->andWhere($anotherQB->expr()->lte('x.position', '(' . $okPermissionsQB->getDQL() . ')'))
        ;

        $or = $anotherQB->expr()->orX();
        foreach ($sufficientLevels as $level) {
            $or->add($anotherQB->expr()->eq  ('x.permissionLevels', $anotherQB->expr()->literal($level)));
            $or->add($anotherQB->expr()->like('x.permissionLevels', $anotherQB->expr()->literal('%,' . $level)));
            $or->add($anotherQB->expr()->like('x.permissionLevels', $anotherQB->expr()->literal($level . ',%')));
            $or->add($anotherQB->expr()->like('x.permissionLevels', $anotherQB->expr()->literal('%,' . $level . ',%')));
        }
        $anotherQB->andWhere($or);

        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
            ->from('CmfcmfMediaModule:Collection\CollectionEntity', 'c')
            ->where($qb->expr()->exists($anotherQB->getDQL()))
        ;
        $qb->setParameters($okPermissionsQB->getParameters());

        return $qb;
    }

    /**
     * @return SecurityCategory[]
     */
    public function getCollectionSecurityCategories()
    {
        return SecurityTree::getCategories($this->translator, $this->domain);
    }

    /**
     * @return QueryBuilder
     */
    private function getParentCollectionsQB($alias)
    {
        $parentCollectionsQB = $this->em->createQueryBuilder();
        $parentCollectionsQB->select($alias)
            ->from('CmfcmfMediaModule:Collection\CollectionEntity', $alias)
            ->where($parentCollectionsQB->expr()->lte("$alias.lft", 'c.lft'))
            ->andWhere($parentCollectionsQB->expr()->gte("$alias.rgt", 'c.rgt'))
            ->orderBy("$alias.lft", 'ASC')
            ->andWhere($parentCollectionsQB->expr()->eq("$alias.root", 'c.root'));

        return $parentCollectionsQB;
    }
}
