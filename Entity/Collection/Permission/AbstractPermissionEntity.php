<?php

declare(strict_types=1);

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
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\AbstractPermissionRestrictionEntity;
use Cmfcmf\Module\MediaModule\Traits\StandardFieldsTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
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
    use StandardFieldsTrait;

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
     * @ORM\ManyToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\Permission\Restriction\AbstractPermissionRestrictionEntity",
     *     inversedBy="permissions", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinTable(name="cmfcmfmedia_permission_permission_restriction")
     *
     * No assertions.
     *
     * @var AbstractPermissionRestrictionEntity[]|ArrayCollection
     */
    protected $restrictions;

    public function __construct()
    {
        $this->description = '';
        $this->goOn = false;
        $this->appliedToSubCollections = true;
        $this->appliedToSelf = true;
        $this->restrictions = new ArrayCollection();
        $this->locked = false;
    }

    /**
     * Make sure at least one of appliedToSelf and appliedToSubCollections is set.
     *
     * @Assert\Callback
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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
     */
    public function setPermissionLevels($permissionLevels): self
    {
        $this->permissionLevels = $permissionLevels;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getCollection(): ?CollectionEntity
    {
        return $this->collection;
    }

    public function setCollection(CollectionEntity $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getValidAfter(): ?DateTime
    {
        return $this->validAfter;
    }

    public function setValidAfter(?DateTime $validAfter): self
    {
        $this->validAfter = $validAfter;

        return $this;
    }

    public function getValidUntil(): ?DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(?DateTime $validUntil): self
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function isGoOn(): bool
    {
        return $this->goOn;
    }

    public function setGoOn(bool $goOn): self
    {
        $this->goOn = $goOn;

        return $this;
    }

    public function isAppliedToSelf(): bool
    {
        return $this->appliedToSelf;
    }

    public function setAppliedToSelf(bool $appliedToSelf): self
    {
        $this->appliedToSelf = $appliedToSelf;

        return $this;
    }

    public function isAppliedToSubCollections(): bool
    {
        return $this->appliedToSubCollections;
    }

    public function setAppliedToSubCollections(bool $appliedToSubCollections): self
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

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
