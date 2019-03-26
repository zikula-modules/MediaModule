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

namespace Cmfcmf\Module\MediaModule\Entity\Collection;

use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_category_collection")
 */
class CollectionCategoryAssignmentEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="categoryAssignments")
     *
     * @var CollectionEntity
     */
    private $entity;

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
