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

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Repository;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CollectionRepository extends NestedTreeRepository
{
    /**
     * @return CollectionEntity
     */
    public function getRootNode()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('c')
            ->where($qb->expr()->isNull('c.parent'))
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
