<?php

namespace Cmfcmf\Module\MediaModule\Entity\Permission;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Sortable\Sortable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_permission")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 * @ORM\DiscriminatorMap({
 *  "group"    = "GroupPermissionEntity",
 *  "user"     = "UserPermissionEntity",
 *  "password" = "PasswordPermissionEntity"
 * })
 */
abstract class AbstractPermissionEntity implements Sortable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
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
    private $version;

    /**
     * @ORM\Column(type="string", length=511)
     * @Assert\NotBlank()
     * @Assert\Length(max="511")
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $permissionLevel;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Permission\CollectionPermissionEntity", mappedBy="permission", orphanRemoval=true)
     *
     * @var CollectionPermissionEntity[]|ArrayCollection
     */
    protected $collectionMappings;

    public function __construct()
    {
        $this->permissionLevel = 0;
        $this->description = "";
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
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param int $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

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
     * @param string $description
     *
     * @return AbstractPermissionEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $permissionLevel
     *
     * @return AbstractPermissionEntity
     */
    public function setPermissionLevel($permissionLevel)
    {
        $this->permissionLevel = $permissionLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getPermissionLevel()
    {
        return $this->permissionLevel;
    }
}
