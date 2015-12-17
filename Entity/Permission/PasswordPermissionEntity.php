<?php

namespace Cmfcmf\Module\MediaModule\Entity\Permission;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This allows to set a password.
 */
class PasswordPermissionEntity extends AbstractPermissionEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $password;

    public function __construct()
    {
        parent::__construct();

        $this->password = "";
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return PasswordPermissionEntity
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}