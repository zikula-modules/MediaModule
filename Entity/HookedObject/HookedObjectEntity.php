<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\HookedObject;

use Cmfcmf\Module\MediaModule\CollectionTemplate\TemplateInterface;
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Core\UrlInterface;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\HookedObject\Repository\HookedObjectRepository")
 * @ORM\Table(name="cmfcmfmedia_hookedobject")
 */
class HookedObjectEntity
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
     * @ORM\ManyToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity",
     *     inversedBy="hookedObjects")
     * @ORM\JoinTable(name="cmfcmfmedia_hookedobject_license")
     *
     * No assertions.
     *
     * @var LicenseEntity[]|ArrayCollection
     */
    private $licenses;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity",
     *     mappedBy="hookedObject", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * No assertions.
     *
     * @var HookedObjectCollectionEntity[]|ArrayCollection
     */
    private $hookedObjectCollections;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectMediaEntity",
     *     mappedBy="hookedObject", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * No assertions.
     *
     * @var HookedObjectMediaEntity[]|ArrayCollection
     */
    private $hookedObjectMedia;

    /**
     * @ORM\Column(length=50)
     *
     * No assertions.
     *
     * @var string
     */
    private $module;

    /**
     * @ORM\Column(type="integer")
     *
     * No assertions.
     *
     * @var int
     */
    private $areaId;

    /**
     * @ORM\Column(type="integer")
     *
     * No assertions.
     *
     * @var int
     */
    private $objectId;

    /**
     * @ORM\Column(type="object", nullable=true)
     *
     * No assertions.
     *
     * @var UrlInterface
     */
    private $urlObject = null;

    public function __construct(Hook $hook)
    {
        $this->setModule($hook->getCaller());
        $this->setAreaId($hook->getAreaId());
        $this->setObjectId($hook->getId());
        if ($hook instanceof ProcessHook || $hook instanceof DisplayHook) {
            $this->setUrlObject($hook->getUrl());
        }

        $this->licenses = new ArrayCollection();
        $this->hookedObjectCollections = new ArrayCollection();
        $this->hookedObjectMedia = new ArrayCollection();
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
     * @return HookedObjectEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return LicenseEntity[]|ArrayCollection
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param LicenseEntity[]|ArrayCollection $licenses
     *
     * @return HookedObjectEntity
     */
    public function setLicenses($licenses)
    {
        $this->licenses = $licenses;

        return $this;
    }

    /**
     * @return HookedObjectCollectionEntity[]|ArrayCollection
     */
    public function getHookedObjectCollections()
    {
        return $this->hookedObjectCollections;
    }

    /**
     * @param HookedObjectCollectionEntity[]|ArrayCollection $hookedObjectCollections
     *
     * @return HookedObjectEntity
     */
    public function setHookedObjectCollections($hookedObjectCollections)
    {
        $this->hookedObjectCollections = $hookedObjectCollections;

        return $this;
    }

    /**
     * @return HookedObjectMediaEntity[]|ArrayCollection
     */
    public function getHookedObjectMedia()
    {
        return $this->hookedObjectMedia;
    }

    /**
     * @param HookedObjectMediaEntity[]|ArrayCollection $hookedObjectMedia
     *
     * @return HookedObjectEntity
     */
    public function setHookedObjectMedia($hookedObjectMedia)
    {
        $this->hookedObjectMedia = $hookedObjectMedia;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     *
     * @return HookedObjectEntity
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * @param int $areaId
     *
     * @return HookedObjectEntity
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return HookedObjectEntity
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return UrlInterface
     */
    public function getUrlObject()
    {
        return $this->urlObject;
    }

    /**
     * @param UrlInterface $urlObject
     *
     * @return HookedObjectEntity
     */
    public function setUrlObject($urlObject)
    {
        $this->urlObject = $urlObject;

        return $this;
    }

    /**
     * Adds the given license to this HookedObject.
     *
     * @param LicenseEntity $licenseEntity
     */
    public function addLicense(LicenseEntity $licenseEntity)
    {
        $licenseEntity->getHookedObjects()->add($this);
        $this->licenses->add($licenseEntity);
    }

    /**
     * Adds the given medium to this HookedObject.
     *
     * @param AbstractMediaEntity $mediaEntity
     */
    public function addMedium(AbstractMediaEntity $mediaEntity)
    {
        $hookedObjectMedia = new HookedObjectMediaEntity();
        $hookedObjectMedia->setMedia($mediaEntity)->setHookedObject($this);

        $mediaEntity->getHookedObjectMedia()->add($hookedObjectMedia);
        $this->hookedObjectMedia->add($hookedObjectMedia);
    }

    /**
     * Adds the given collection to this HookedObject. Also includes the template to use
     * and whether or not parent and child collections shall be shown.
     *
     * @param CollectionEntity       $collectionEntity
     * @param TemplateInterface|null $template
     * @param bool                   $showParentCollection
     * @param bool                   $showChildCollections
     */
    public function addCollection(
        CollectionEntity $collectionEntity,
        TemplateInterface $template = null,
        $showParentCollection,
        $showChildCollections
    ) {
        $hookedObjectCollection = new HookedObjectCollectionEntity(
            $template, $showParentCollection, $showChildCollections
        );
        $hookedObjectCollection->setCollection($collectionEntity)->setHookedObject($this);

        $collectionEntity->getHookedObjectCollections()->add($hookedObjectCollection);
        $this->hookedObjectCollections->add($hookedObjectCollection);
    }

    /**
     * Removes all hooked licenses from this HookedObject.
     */
    public function clearLicenses()
    {
        foreach ($this->licenses as $licenseEntity) {
            $licenseEntity->getHookedObjects()->remove($this->getId());
        }

        $this->licenses->clear();
    }

    /**
     * Removes all hooked media from this HookedObject.
     */
    public function clearMedia()
    {
        foreach ($this->hookedObjectMedia as $hookedObjectMediaEntity) {
            $hookedObjectMediaEntity
                ->getMedia()->getHookedObjectMedia()->removeElement($hookedObjectMediaEntity);
            $hookedObjectMediaEntity->setMedia(null)->setHookedObject(null);
        }

        $this->hookedObjectMedia->clear();
    }

    /**
     * Removes all hooked collections from this HookedObject.
     */
    public function clearCollections()
    {
        foreach ($this->hookedObjectCollections as$hookedObjectCollectionEntity) {
            $hookedObjectCollectionEntity
                ->getCollection()->getHookedObjectCollections()->removeElement(
                    $hookedObjectCollectionEntity
                );
            $hookedObjectCollectionEntity->setCollection(null)->setHookedObject(null);
        }

        $this->hookedObjectCollections->clear();
    }
}
