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

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * This allows to set access rights for all groups or for one to many groups.
 */
class GroupPermissionEntity extends AbstractPermissionEntity
{
    /**
     * @ORM\Column(type="simple_array")
     *
     * No assertions.
     *
     * @var int[]
     */
    protected $groupIds;

    public function __construct()
    {
        parent::__construct();

        $this->groupIds = [];
    }

    /**
     * @param \int[] $groupIds
     *
     * @return GroupPermissionEntity
     */
    public function setGroupIds($groupIds)
    {
        $this->groupIds = $groupIds;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTargetingAllGroups()
    {
        return 0 === count($this->groupIds);
    }

    /**
     * @return \int[]
     */
    public function getGroupIds()
    {
        return $this->groupIds;
    }
}
