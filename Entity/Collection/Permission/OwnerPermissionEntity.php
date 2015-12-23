<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This allows to set access rights of the owner.
 */
class OwnerPermissionEntity extends AbstractPermissionEntity
{
}
