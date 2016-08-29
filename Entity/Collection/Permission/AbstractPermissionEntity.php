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

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sortable\Sortable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Repository\PermissionRepository")
 * @ORM\Table(name="cmfcmfmedia_permission")
 *
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 * @ORM\DiscriminatorMap({
 *  "group"    = "GroupPermissionEntity",
 *  "user"     = "UserPermissionEntity",
 *  "owner"    = "OwnerPermissionEntity",
 *  "password" = "PasswordPermissionEntity"
 * })
 */
abstract class AbstractPermissionEntity implements Sortable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * No assertions.
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * No assertions.
     *
     * @var int
     */
    protected $version;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition()
     *
     * No assertions.
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\Column(type="string", length=511, nullable=true)
     *
     * @Assert\Length(max="511")
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="simple_array", name="permissionLevel")
     *
     * @Assert\Count(min=1, minMessage="You need to grant at least one permission level.")
     *
     * @var string[]
     */
    protected $permissionLevels;

    /**
     * @ORM\Column(type="boolean")
     *
     * See the validate function for assertions.
     *
     * @var bool
     */
    protected $appliedToSelf;

    /**
     * @ORM\Column(type="boolean")
     *
     * See the validate function for assertions.
     *
     * @var bool
     */
    protected $appliedToSubCollections;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    protected $goOn;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime()
     *
     * @var \DateTime|null
     */
    protected $validAfter;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime()
     *
     * @var \DateTime|null
     */
    protected $validUntil;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    protected $locked;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="permissions")
     *
     * No assertions.
     *
     * @var CollectionEntity
     */
    protected $collection;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     *
     * No assertions.
     *
     * @var int.
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     *
     * No assertions.
     *
     * @var int.
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * No assertions.
     *
     * @var \DateTime.
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * No assertions.
     *
     * @var \DateTime.
     */
    protected $updatedDate;

    public function __construct()
    {
        $this->description = "";
        $this->goOn = false;
        $this->appliedToSubCollections = true;
        $this->appliedToSelf = true;
        $this->locked = false;
    }

    /**
     * Make sure at least one of appliedToSelf and appliedToSubCollections is set.
     *
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (!$this->appliedToSelf && !$this->appliedToSubCollections) {
            $context->buildViolation('The permissions must apply to either itself, it\'s sub-collections or both.')
                ->atPath('appliedToSelf')
                ->addViolation()
            ;
            $context->buildViolation('The permissions must apply to either itself, it\'s sub-collections or both.')
                ->atPath('appliedToSubCollections')
                ->addViolation()
            ;
        }
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPermissionLevels()
    {
        return $this->permissionLevels;
    }

    /**
     * @param string[] $permissionLevels
     *
     * @return $this
     */
    public function setPermissionLevels($permissionLevels)
    {
        $this->permissionLevels = $permissionLevels;

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
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

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
     *
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidAfter()
    {
        return $this->validAfter;
    }

    /**
     * @param \DateTime|null $validAfter
     *
     * @return $this
     */
    public function setValidAfter($validAfter)
    {
        $this->validAfter = $validAfter;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @param \DateTime|null $validUntil
     *
     * @return $this
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGoOn()
    {
        return $this->goOn;
    }

    /**
     * @param bool $goOn
     *
     * @return $this
     */
    public function setGoOn($goOn)
    {
        $this->goOn = $goOn;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppliedToSelf()
    {
        return $this->appliedToSelf;
    }

    /**
     * @param bool $appliedToSelf
     *
     * @return $this
     */
    public function setAppliedToSelf($appliedToSelf)
    {
        $this->appliedToSelf = $appliedToSelf;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppliedToSubCollections()
    {
        return $this->appliedToSubCollections;
    }

    /**
     * @param bool $appliedToSubCollections
     *
     * @return $this
     */
    public function setAppliedToSubCollections($appliedToSubCollections)
    {
        $this->appliedToSubCollections = $appliedToSubCollections;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return $this
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedUserId()
    {
        return $this->createdUserId;
    }

    /**
     * @param int $createdUserId
     *
     * @return $this
     */
    public function setCreatedUserId($createdUserId)
    {
        $this->createdUserId = $createdUserId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedUserId()
    {
        return $this->updatedUserId;
    }

    /**
     * @param int $updatedUserId
     *
     * @return $this
     */
    public function setUpdatedUserId($updatedUserId)
    {
        $this->updatedUserId = $updatedUserId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     *
     * @return $this
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param \DateTime $updatedDate
     *
     * @return $this
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }
}
