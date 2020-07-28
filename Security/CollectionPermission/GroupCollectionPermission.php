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

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\GroupPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * Group based collection permission.
 */
class GroupCollectionPermission extends AbstractCollectionPermission
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    public function __construct(
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($translator, $currentUserApi);
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Group', [], 'cmfcmfmediamodule');
    }

    /**
     * @param GroupPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        $targets = [];
        if (!count($permissionEntity->getGroupIds())) {
            return '';
        }

        $groupNames = $this->groupRepository->getGroupNamesById();
        foreach ($permissionEntity->getGroupIds() as $groupId) {
            if (-1 === $groupId) {
                $targets[] = $this->translator->trans('All groups', [], 'cmfcmfmediamodule');
            } elseif (isset($groupNames[$groupId])) {
                $targets[] = $groupNames[$groupId];
            }
        }

        return implode(', ', $targets);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias)
    {
        if ('cli' === PHP_SAPI) {
            return null;
        }

        $groupIds = [];
        foreach ($this->currentUserApi->get('groups') as $group) {
            $groupIds[] = $group->getGid();
        }
        $groupIds[] = -1;

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_gp", Expr\Join::WITH, "${permissionAlias}.id = {$permissionAlias}_gp.id");

        $or = $qb->expr()->orX();
        foreach ($groupIds as $c => $groupId) {
            $or->add(self::whereInSimpleArray($qb, "{$permissionAlias}_gp", "group${c}", $groupId, 'groupIds'));
        }

        return $or;
    }
}
