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

namespace Cmfcmf\Module\MediaModule\Traits;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Standard fields trait.
 */
trait StandardFieldsTrait
{
    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(referencedColumnName="uid")
     * @var UserEntity
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Assert\Type("\DateTimeInterface")
     * @var DateTimeInterface $createdDate
     */
    protected $createdDate;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(referencedColumnName="uid")
     * @var UserEntity
     */
    protected $updatedBy;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     * @Assert\Type("\DateTimeInterface")
     * @var DateTimeInterface $updatedDate
     */
    protected $updatedDate;

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserEntity $createdBy = null): void
    {
        if ($this->createdBy !== $createdBy) {
            $this->createdBy = $createdBy;
        }
    }

    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(DateTimeInterface $createdDate = null): void
    {
        if ($this->createdDate !== $createdDate) {
            $this->createdDate = $createdDate;
        }
    }

    public function getUpdatedBy(): ?UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserEntity $updatedBy = null): void
    {
        if ($this->updatedBy !== $updatedBy) {
            $this->updatedBy = $updatedBy;
        }
    }

    public function getUpdatedDate(): ?DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(DateTimeInterface $updatedDate = null): void
    {
        if ($this->updatedDate !== $updatedDate) {
            $this->updatedDate = $updatedDate;
        }
    }
}
