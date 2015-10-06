<?php

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
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity", inversedBy="hookedObjectCollections")
     * @var HookedObjectEntity
     */
    private $hookedObject;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="hookedObjectCollections")
     * @var CollectionEntity
     */
    private $collection;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @var string
     */
    private $template;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $showParentCollections;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $showChildCollections;

    public function __construct($template, $showParentCollections = false, $showChildCollection = true)
    {
        $this->template = $template;
        $this->showParentCollections = $showParentCollections;
        $this->showChildCollections = $showChildCollection;
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
     * @return HookedObjectCollectionEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return HookedObjectEntity
     */
    public function getHookedObject()
    {
        return $this->hookedObject;
    }

    /**
     * @param HookedObjectEntity $hookedObject
     * @return HookedObjectCollectionEntity
     */
    public function setHookedObject($hookedObject)
    {
        $this->hookedObject = $hookedObject;
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
     * @return HookedObjectCollectionEntity
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return HookedObjectCollectionEntity
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowParentCollections()
    {
        return $this->showParentCollections;
    }

    /**
     * @param boolean $showParentCollections
     * @return HookedObjectCollectionEntity
     */
    public function setShowParentCollections($showParentCollections)
    {
        $this->showParentCollections = $showParentCollections;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowChildCollections()
    {
        return $this->showChildCollections;
    }

    /**
     * @param boolean $showChildCollections
     * @return HookedObjectCollectionEntity
     */
    public function setShowChildCollections($showChildCollections)
    {
        $this->showChildCollections = $showChildCollections;
        return $this;
    }
}
