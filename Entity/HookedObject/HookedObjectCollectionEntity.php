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

namespace Cmfcmf\Module\MediaModule\Entity\HookedObject;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_hookedobject_collection")
 */
class HookedObjectCollectionEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * No assertions.
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity",
     *     inversedBy="hookedObjectCollections")
     *
     * No assertions.
     *
     * @var HookedObjectEntity
     */
    private $hookedObject;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity",
     *     inversedBy="hookedObjectCollections")
     *
     * No assertions.
     *
     * @var CollectionEntity
     */
    private $collection;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * No assertions.
     *
     * @var string
     */
    private $template;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    private $showParentCollections;

    /**
     * @ORM\Column(type="boolean")
     *
     * No assertions.
     *
     * @var bool
     */
    private $showChildCollections;

    public function __construct(
        $template,
        $showParentCollections = false,
        $showChildCollection = true
    ) {
        $this->template = $template;
        $this->showParentCollections = $showParentCollections;
        $this->showChildCollections = $showChildCollection;
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

    public function getHookedObject(): ?HookedObjectEntity
    {
        return $this->hookedObject;
    }

    public function setHookedObject(HookedObjectEntity $hookedObject): self
    {
        $this->hookedObject = $hookedObject;

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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function isShowParentCollections(): bool
    {
        return $this->showParentCollections;
    }

    public function setShowParentCollections(bool $showParentCollections): self
    {
        $this->showParentCollections = $showParentCollections;

        return $this;
    }

    public function isShowChildCollections(): bool
    {
        return $this->showChildCollections;
    }

    public function setShowChildCollections(bool $showChildCollections): self
    {
        $this->showChildCollections = $showChildCollections;

        return $this;
    }
}
