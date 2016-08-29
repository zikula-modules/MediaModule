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

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\PasswordPermissionEntity;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class PasswordCollectionPermission extends AbstractCollectionPermission
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->translator->trans('Password', [], 'cmfcmfmediamodule');
    }

    /**
     * @param PasswordPermissionEntity $permissionEntity
     *
     * @return string
     */
    public function getTargets($permissionEntity)
    {
        return $this->getTitle() . ': ' . $permissionEntity->getPassword();
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicablePermissionsExpression(QueryBuilder &$qb, $permissionAlias, $allPasswordsValid = false)
    {
        $request = $this->requestStack->getMasterRequest();
        if ($request === null) {
            return null;
        }
        $session = $request->getSession();
        if ($session === null) {
            return null;
        }

        $passwords = $session->get('cmfcmfmediamodule_passwords', []);

        $qb->leftJoin($this->getEntityClass(), "{$permissionAlias}_pp", Expr\Join::WITH, "$permissionAlias.id = {$permissionAlias}_pp.id");

        if ($allPasswordsValid) {
            return $qb->expr()->eq('1', '1');
        }

        $orX = $qb->expr()->orX();
        foreach ($passwords as $collectionId => $password) {
            $orX->add(
                $qb->expr()->andX(
                    #$qb->expr()->eq("c.id", ':password_' . (int)$collectionId . '_c'),
                    $qb->expr()->eq("{$permissionAlias}_pp.password", ':password_' . (int)$collectionId . '_p')
                )
            );
            #$qb->setParameter('password_' . (int)$collectionId . '_c', $collectionId);
            $qb->setParameter('password_' . (int)$collectionId . '_p', $password);
        }

        return $orX;
    }
}
