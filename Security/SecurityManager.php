<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionCategory;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionContainer;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
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
     * @var CollectionPermissionSecurityTree
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
    ) {
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

        return $this->permissionApi->hasPermission(
            "CmfcmfMediaModule:$type:",
            "$id::",
            $this->levels[$action]
        );
    }

    /**
     * Do a plain, old security check.
     *
     * @param $component
     * @param $instance
     * @param $level
     *
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
     *
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
            $this->collectionSecurityGraph = CollectionPermissionSecurityTree::createGraph(
                $this->translator,
                $this->domain
            );
        }

        return $this->collectionSecurityGraph;
    }

    public function getCollectionsWithAccessQueryBuilder($requestedLevel)
    {
        return $this->getAccessQueryBuilder($requestedLevel, 'collections');
    }

    public function getMediaWithAccessQueryBuilder($requestedLevel)
    {
        return $this->getAccessQueryBuilder($requestedLevel, 'media');
    }

    /**
     * Return all collections which the current user has $permLevel access to.
     *
     * @param int    $requestedLevel The requested permission level. Must be one of the constants from
     *                               the CollectionPermissionSecurityTree class.
     * @param string $type           (media|collections) Will either return media joined by collections or
     *                               collections only.
     *
     * @return QueryBuilder Select all collections where there is at least on permission which
     *
     * Select all collections where there is at least on permission which
     * 1. is associated to the collection itself or a parent collection.
     * 2. includes the required permission level or one of it's parent permission levels
     * 3. is currently valid (validAfter <= now < validUntil)
     * 4. has appliedToSelf = 1 and is associated to the collection itself.
     * OR
     * has appliedToSubCollections = 1 and is associated to a parent collection.
     * 5. applies to the current user.
     * 6. has a position which is lower or equal to the position of the first permission having
     * goOn = 0 which applies to the collection.
     */
    private function getAccessQueryBuilder($requestedLevel, $type)
    {
        if (!in_array($type, ['media', 'collections'], true)) {
            throw new \DomainException();
        }

        $now = new \DateTime();
        $securityGraph = $this->getCollectionSecurityGraph();

        // This contains all permission levels which are sufficient to grant $requestedLevel access.
        // It will contain $requestedLevel itself and all other levels which require $requestedLevel.
        $sufficientLevels = array_keys(
            $securityGraph->getVerticesRequiring($securityGraph->getVertex($requestedLevel))->getMap()
        );
        $sufficientLevels[] = $requestedLevel;

        //
        // First query builder.
        // This one is responsible for point 6 in the docblock.
        // It returns the first permission which applies to a collection and has goOn = 0.
        // The granted permission level WILL NOT be checked and can be lower than the requested level.
        //
        $firstPositionWithoutGoOnQB = $this->em->createQueryBuilder();
        $firstPositionWithoutGoOnQB
            ->select($firstPositionWithoutGoOnQB->expr()->min('p.position'))
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'p')
            ->leftJoin('p.collection', 'collectionOfPermission');

        // Filter the permissions, so that only the ones which belong to the current user are returned.
        $currentUserOR = $firstPositionWithoutGoOnQB->expr()->orX();
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        foreach ($collectionPermissions as $collectionPermission) {
            $currentUserOR->add(
                $collectionPermission->getApplicablePermissionsExpression(
                    $firstPositionWithoutGoOnQB,
                    'p'
                )
            );
        }

        $firstPositionWithoutGoOnQB
            // 6.1. is associated to the collection itself or a parent collection.
            ->where(
            // Get all permissions of the collection itself + all it's parent collections.
                $firstPositionWithoutGoOnQB->expr()->in(
                    'collectionOfPermission.id',
                    // Retrieve the ids of all parent collections + the collection itself.
                    $this->getParentCollectionsQB(
                        'parentCollectionsOfcollectionOfPermission'
                    )->getDQL()
                )
            )
            // 6.2. is currently valid (validAfter <= now < validUntil)
            ->andWhere(
                $firstPositionWithoutGoOnQB->expr()->andX(
                    $firstPositionWithoutGoOnQB->expr()->orX(
                        $firstPositionWithoutGoOnQB->expr()->isNull('p.validAfter'),
                        $firstPositionWithoutGoOnQB->expr()->lte('p.validAfter', ':now')
                    ),
                    $firstPositionWithoutGoOnQB->expr()->orX(
                        $firstPositionWithoutGoOnQB->expr()->isNull('p.validUntil'),
                        $firstPositionWithoutGoOnQB->expr()->gt('p.validUntil', ':now')
                    )
                )
            )
            ->setParameter('now', $now)
            // 6.3. has appliedToSelf = 1 and is associated to the collection itself.
            //      OR
            //    has appliedToSubCollections = 1 and is associated to a parent collection.
            ->andWhere(
            // Make sure the permission applies.
                $firstPositionWithoutGoOnQB->expr()->orX(
                    $firstPositionWithoutGoOnQB->expr()->andX(
                    // For permissions of the collection itself, the "appliesToSelf" flag must be set.
                        $firstPositionWithoutGoOnQB->expr()->eq(
                            'collectionOfPermission.id',
                            'c.id'
                        ),
                        $firstPositionWithoutGoOnQB->expr()->eq('p.appliedToSelf', true)
                    ),
                    $firstPositionWithoutGoOnQB->expr()->andX(
                    // For parent collections' permissions, the "appliedToSubCollections" flag must be set.
                        $firstPositionWithoutGoOnQB->expr()->neq(
                            'collectionOfPermission.id',
                            'c.id'
                        ),
                        $firstPositionWithoutGoOnQB->expr()->eq('p.appliedToSubCollections', true)
                    )
                )
            )
            // 6.4. applies to the current user.
            ->andWhere($currentUserOR)
            // 6.5. has goOn set to false.
            ->andWhere($firstPositionWithoutGoOnQB->expr()->eq('p.goOn', 0));

        //
        // Second query builder.
        // This one is responsible for point 1 to 6 in the docblock.
        // It returns all the permissions which apply to the current collection but with a position
        // lower than the one returned from the first query builder.
        //
        $applicablePermissionsQB = $this->em->createQueryBuilder();
        $applicablePermissionsQB
            ->select('x')
            ->from('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'x')
            ->leftJoin('x.collection', 'collectionOfPermission2');

        // Create an OR expression which contains all the permission levels which would grant access.
        $permissionLevelsOR = $applicablePermissionsQB->expr()->orX();
        foreach ($sufficientLevels as $level) {
            $permissionLevelsOR->add(
                $applicablePermissionsQB->expr()->eq(
                    'x.permissionLevels',
                    $applicablePermissionsQB->expr()->literal($level)
                )
            );
            $permissionLevelsOR->add(
                $applicablePermissionsQB->expr()->like(
                    'x.permissionLevels',
                    $applicablePermissionsQB->expr()->literal('%,' . $level)
                )
            );
            $permissionLevelsOR->add(
                $applicablePermissionsQB->expr()->like(
                    'x.permissionLevels',
                    $applicablePermissionsQB->expr()->literal($level . ',%')
                )
            );
            $permissionLevelsOR->add(
                $applicablePermissionsQB->expr()->like(
                    'x.permissionLevels',
                    $applicablePermissionsQB->expr()->literal('%,' . $level . ',%')
                )
            );
        }
        // Create an OR expression which contains all the possible user-ids, group-ids, ...
        // which apply to the current user.
        $collectionPermissions = $this->collectionPermissionContainer->getCollectionPermissions();
        $currentUserOR = $applicablePermissionsQB->expr()->orX();
        foreach ($collectionPermissions as $collectionPermission) {
            $currentUserOR->add(
                $collectionPermission->getApplicablePermissionsExpression(
                    $applicablePermissionsQB,
                    'x'
                )
            );
        }

        $applicablePermissionsQB
            // 1. is associated to the collection itself or a parent collection.
            ->andWhere(
                $applicablePermissionsQB->expr()->in(
                    'collectionOfPermission2.id',
                    // Retrieve the ids of all parent collections + the collection itself.
                    $this->getParentCollectionsQB(
                        'parentCollectionsOfcollectionOfPermission2'
                    )->getDQL()
                )
            )
            // 2. includes the required permission level or one of it's parent permission levels
            ->andWhere($permissionLevelsOR)
            // 3. is currently valid (validAfter <= now < validUntil)
            ->andWhere(
                $applicablePermissionsQB->expr()->andX(
                    $applicablePermissionsQB->expr()->orX(
                        $applicablePermissionsQB->expr()->isNull('x.validAfter'),
                        $applicablePermissionsQB->expr()->lte('x.validAfter', ':now')
                    ),
                    $applicablePermissionsQB->expr()->orX(
                        $applicablePermissionsQB->expr()->isNull('x.validUntil'),
                        $applicablePermissionsQB->expr()->gt('x.validUntil', ':now')
                    )
                )
            )
            ->setParameter('now', $now)
            // 4. has appliedToSelf = 1 and is associated to the collection itself.
            //      OR
            //    has appliedToSubCollections = 1 and is associated to a parent collection.
            ->andWhere(
                $applicablePermissionsQB->expr()->orX(
                    $applicablePermissionsQB->expr()->andX(
                    // For permissions of the collection itself, the "appliesToSelf" flag must be set.
                        $applicablePermissionsQB->expr()->eq('collectionOfPermission2.id', 'c.id'),
                        $applicablePermissionsQB->expr()->eq('x.appliedToSelf', true)
                    ),
                    $applicablePermissionsQB->expr()->andX(
                    // For parent collections' permissions, the "appliedToSubCollections" flag must be set.
                        $applicablePermissionsQB->expr()->neq('collectionOfPermission2.id', 'c.id'),
                        $applicablePermissionsQB->expr()->eq('x.appliedToSubCollections', true)
                    )
                )
            )
            // 5. applies to the current user.
            ->andWhere($currentUserOR)
            // 6. has a position which is lower or equal to the position of the first permission having goOn = 0
            // which applies to the collection.
            ->andWhere(
                $applicablePermissionsQB->expr()->lte(
                    'x.position',
                    '(' . $firstPositionWithoutGoOnQB->getDQL() . ')'
                )
            );

        //
        // Third query builder
        // This one returns all collections where there exists at least one permission returned
        // by the second query builder.
        // Or, if $type == media, returns all the media which are within a collection where
        // there exists at least one permission returned by the second query builder.
        //
        $qb = $this->em->createQueryBuilder();
        if ($type == 'collections') {
            $qb->select('c')
                ->from('CmfcmfMediaModule:Collection\CollectionEntity', 'c');
        } elseif ($type == 'media') {
            $qb->select('m')
                ->from('CmfcmfMediaModule:Media\AbstractMediaEntity', 'm')
                ->leftJoin('m.collection', 'c');
        } else {
            throw new \LogicException();
        }
        $qb->where($qb->expr()->exists($applicablePermissionsQB->getDQL()))
            ->setParameters($firstPositionWithoutGoOnQB->getParameters());

        return $qb;
    }

    /**
     * @return CollectionPermissionCategory[]
     */
    public function getCollectionSecurityCategories()
    {
        return CollectionPermissionSecurityTree::getCategories($this->translator, $this->domain);
    }

    /**
     * @param $alias
     *
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
