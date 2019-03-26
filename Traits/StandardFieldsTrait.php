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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * Standard fields trait implementation class.
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
     * @Assert\DateTime()
     * @var \DateTimeInterface
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
     * @Assert\DateTime()
     * @var \DateTimeInterface
     */
    protected $updatedDate;

    /**
     * Returns the created by.
     *
     * @return UserEntity
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets the created by.
     *
     * @param UserEntity $createdBy
     *
     * @return void
     */
    public function setCreatedBy($createdBy)
    {
        if ($this->createdBy !== $createdBy) {
            $this->createdBy = $createdBy;
        }
    }

    /**
     * Returns the created date.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Sets the created date.
     *
     * @param \DateTimeInterface $createdDate
     *
     * @return void
     */
    public function setCreatedDate($createdDate)
    {
        if ($this->createdDate !== $createdDate) {
            $this->createdDate = $createdDate;
        }
    }

    /**
     * Returns the updated by.
     *
     * @return UserEntity
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Sets the updated by.
     *
     * @param UserEntity $updatedBy
     *
     * @return void
     */
    public function setUpdatedBy($updatedBy)
    {
        if ($this->updatedBy !== $updatedBy) {
            $this->updatedBy = $updatedBy;
        }
    }

    /**
     * Returns the updated date.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Sets the updated date.
     *
     * @param \DateTimeInterface $updatedDate
     *
     * @return void
     */
    public function setUpdatedDate($updatedDate)
    {
        if ($this->updatedDate !== $updatedDate) {
            $this->updatedDate = $updatedDate;
        }
    }
}
