<?php

namespace Cmfcmf\Module\MediaModule\Entity\Permission;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
        return count($this->groupIds) == 0;
    }

    /**
     * @return \int[]
     */
    public function getGroupIds()
    {
        return $this->groupIds;
    }
}