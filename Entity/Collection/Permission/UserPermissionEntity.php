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
     * @return \int[]
     */
    public function getUserIds()
    {
        return $this->userIds;
    }

    /**
     * @param \int[] $userIds
     */
    public function setUserIds($userIds): self
    {
        $this->userIds = $userIds;

        return $this;
    }
}
