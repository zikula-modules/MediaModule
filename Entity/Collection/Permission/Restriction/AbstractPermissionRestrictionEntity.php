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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function setShared(bool $shared): self
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
     *
     * @return AbstractPermissionRestrictionEntity
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }
}
