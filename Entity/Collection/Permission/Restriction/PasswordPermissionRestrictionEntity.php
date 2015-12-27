<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * This allows to set a password.
 */
class PasswordPermissionRestrictionEntity extends AbstractPermissionRestrictionEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
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
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}
