<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This allows to set access rights for one to many users.
 */
class UserPermissionEntity extends AbstractPermissionEntity
{
    /**
     * @ORM\Column(type="simple_array")
     *
     * @Assert\Count(min="1", minMessage="You must select at least one user to apply the permission to.")
     *
     * @var int[]
     */
    protected $userIds;

    public function __construct()
    {
        parent::__construct();

        $this->userIds = [];
    }

    /**
     * @param \int[] $userIds
     *
     * @return UserPermissionEntity
     */
    public function setUserIds($userIds)
    {
        $this->userIds = $userIds;

        return $this;
    }

    /**
     * @return \int[]
     */
    public function getUserIds()
    {
        return $this->userIds;
    }
}
