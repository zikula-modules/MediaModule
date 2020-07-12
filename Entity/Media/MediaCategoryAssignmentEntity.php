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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_category_media")
 */
class MediaCategoryAssignmentEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity", inversedBy="categoryAssignments")
     *
     * @var AbstractMediaEntity
     */
    private $entity;

    public function getEntity(): AbstractMediaEntity
    {
        return $this->entity;
    }

    public function setEntity($entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
