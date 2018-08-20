<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\UserPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

/**
 * User based collection permission.
 */
class UserCollectionPermission extends AbstractCollectionPermission
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @param TranslatorInterface     $translator
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($translator, $currentUserApi);
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('User', [], 'cmfcmfmediamodule');
    }

    /**
     * @param UserPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        $targets = [];
        if (!count($permissionEntity->getUserIds())) {
            return '';
        }

        $users = $this->userRepository->findByUids($permissionEntity->getUserIds());
        foreach ($users as $user) {
            $targets[] = $user->getUname();
        }

        return implode(', ', $targets);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias)
    {
        if ('cli' === php_sapi_name()) {
            return null;
        }

        if ($this->currentUserApi->isLoggedIn()) {
            $userId = $this->currentUserApi->get('uid');
        } else {
            $userId = UsersConstant::USER_ID_ANONYMOUS;
        }

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_up", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_up.id");

        return self::whereInSimpleArray($qb, "{$permissionAlias}_up", 'user', $userId, 'userIds');
    }
}
