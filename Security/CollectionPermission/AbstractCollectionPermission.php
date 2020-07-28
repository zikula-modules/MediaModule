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

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * Provides convenience methods for collection permissions.
 */
abstract class AbstractCollectionPermission implements CollectionPermissionInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;

    public function __construct(
        TranslatorInterface $translator,
        CurrentUserApiInterface $currentUserApi
    ) {
        $this->translator = $translator;
        $this->currentUserApi = $currentUserApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return mb_strtolower($this->getType());
    }

    /**
     * {@inheritdoc}
     */
    public function getFormClass()
    {
        return 'Cmfcmf\Module\MediaModule\Form\Collection\Permission\\' . $this->getType() . 'PermissionType';
    }

    /**
     * @return string
     */
    protected function getType()
    {
        $class = static::class;

        return mb_substr($class, mb_strrpos($class, '\\') + 1, -mb_strlen('CollectionPermission'));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\\' . $this->getType() . 'PermissionEntity';
    }

    /**
     * @param              $entity
     * @param              $type
     * @param              $value
     * @param              $field
     *
     * @return Expr\Composite
     */
    public static function whereInSimpleArray(QueryBuilder &$qb, $entity, $type, $value, $field)
    {
        $qb->setParameter($type . '1', $value);
        $qb->setParameter($type . '2', "%," . $value);
        $qb->setParameter($type . '3', $value . ",%");
        $qb->setParameter($type . '4', "%," . $value . ",%");

        return $qb->expr()->orX(
            $qb->expr()->eq("${entity}.${field}", ':' . $type . '1'),
            $qb->expr()->like("${entity}.${field}", ':' . $type . '2'),
            $qb->expr()->like("${entity}.${field}", ':' . $type . '3'),
            $qb->expr()->like("${entity}.${field}", ':' . $type . '4')
        );
    }
}
