<?php

namespace Cmfcmf\Module\MediaModule\Entity\Permission;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;

/**
 * @ORM\Entity()
 * @ORM\Table("cmfcmfmedia_collection_permission")
 */
class CollectionPermissionEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="permissionMappings", fetch="EAGER")
     * @Gedmo\SortableGroup()
     *
     * @Assert\NotNull()
     *
     * @var CollectionEntity
     */
    protected $collection;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Permission\AbstractPermissionEntity", inversedBy="collectionMappings", fetch="EAGER")
     *
     * @Assert\NotNull()
     *
     * @var AbstractPermissionEntity
     */
    protected $permission;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition()
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $isInherited;

    public function __construct()
    {
        $this->position = -1;
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
     * @return CollectionPermissionEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return CollectionEntity
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param CollectionEntity $collection
     * @return CollectionPermissionEntity
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return AbstractPermissionEntity
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param AbstractPermissionEntity $permission
     * @return CollectionPermissionEntity
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return CollectionPermissionEntity
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @param boolean $isInherited
     * @return CollectionPermissionEntity
     */
    public function setIsInherited($isInherited)
    {
        $this->isInherited = $isInherited;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsInherited()
    {
        return $this->isInherited;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return !$this->isInherited;
    }
}