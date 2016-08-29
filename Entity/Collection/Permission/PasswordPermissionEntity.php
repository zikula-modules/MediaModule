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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This allows to set access rights using a password.
 */
class PasswordPermissionEntity extends AbstractPermissionEntity
{
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @var string
     */
    protected $password;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return PasswordPermissionEntity
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}
