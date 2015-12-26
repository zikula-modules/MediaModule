<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection\Permission;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\AbstractPermissionRestrictionEntity;
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
 *  "group" = "GroupPermissionEntity",
 *  "user"  = "UserPermissionEntity",
 *  "owner" = "OwnerPermissionEntity"
 * })
 */
abstract class AbstractPermissionEntity implements Sortable
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
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition()
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
     * @var bool
     */
    protected $appliedToSelf;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $appliedToSubCollections;

    /**
     * @ORM\Column(type="boolean")
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
     * @var bool
     */
    protected $locked;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="permissions")
     *
     * @var CollectionEntity
     */
    protected $collection;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\AbstractPermissionRestrictionEntity",
     *     inversedBy="permissions", fetch="EAGER", cascade={"persist"})
     *
     * @var AbstractPermissionRestrictionEntity[]|ArrayCollection
     */
    protected $restrictions;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     *
     * @var int.
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     *
     * @var int.
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime.
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
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
        $this->restrictions = new ArrayCollection();
        $this->locked = false;
    }

    /**
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
     * @return Restriction\AbstractPermissionRestrictionEntity[]|ArrayCollection
     */
    public function getRestrictions()
    {
        return $this->restrictions;
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
