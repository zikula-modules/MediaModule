<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_permission_restriction")
 *
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 * @ORM\DiscriminatorMap({
 *  "password" = "PasswordPermissionRestrictionEntity",
 * })
 */
abstract class AbstractPermissionRestrictionEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * @var int
     */
    protected $version;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $shared;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity",
     *     mappedBy="restrictions")
     *
     * @Assert\Count(min="1")
     *
     * @var AbstractPermissionEntity[]|ArrayCollection
     */
    protected $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->shared = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractPermissionRestrictionEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return AbstractPermissionRestrictionEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return AbstractPermissionRestrictionEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * @param boolean $shared
     * @return AbstractPermissionRestrictionEntity
     */
    public function setShared($shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * @return AbstractPermissionEntity[]|ArrayCollection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param AbstractPermissionEntity[]|ArrayCollection $permissions
     * @return AbstractPermissionRestrictionEntity
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }
}
