<?php

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
 * This allows to set access rights of the owner.
 */
class OwnerPermissionEntity extends AbstractPermissionEntity
{
}
