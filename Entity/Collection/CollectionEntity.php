<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\Collection;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Sluggable;
use Gedmo\Tree\Node;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository")
 * @ORM\Table(name="cmfcmfmedia_collection")
 * @Gedmo\Tree(type="nested")
 * @ORM\HasLifecycleCallbacks()
 */
class CollectionEntity implements Node, Sluggable
{
    const TEMPORARY_UPLOAD_COLLECTION_ID = 1;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * No assertions.
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(length=128, unique=true)
     * @Gedmo\Slug(fields={"title"}, unique=true, handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\TreeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="parentRelationField", value="parent"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="/")
     *      })
     * })
     *
     * No assertions.
     *
     * @var string
     */
    protected $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * No assertions.
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * No assertions.
     *
     * @var string|null
     */
    protected $defaultTemplate;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity",
     *     mappedBy="collection", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * No assertions.
     *
     * @var AbstractMediaEntity[]|ArrayCollection
     **/
    protected $media;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity",
     *     mappedBy="collection", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * No assertions.
     *
     * @var HookedObjectCollectionEntity[]|ArrayCollection
     */
    protected $hookedObjectCollections;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity",
     *     mappedBy="collection", fetch="LAZY", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position"="ASC"})
     *
     * No assertions.
     *
     * @var AbstractPermissionEntity[]|ArrayCollection
     */
    protected $permissions;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionCategoryAssignmentEntity",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     *
     * @var ArrayCollection|CollectionCategoryAssignmentEntity[]
     */
    private $categoryAssignments;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     *
     * No assertions.
     *
     * @var int
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     *
     * No assertions.
     *
     * @var int
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * No assertions.
     *
     * @var \DateTime
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * No assertions.
     *
     * @var \DateTime
     */
    protected $updatedDate;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * No assertions.
     *
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(name="lft", type="integer")
     * @Gedmo\TreeLeft
     *
     * No assertions.
     *
     * @var int
     */
    private $lft;

    /**
     * @ORM\Column(name="rgt", type="integer")
     * @Gedmo\TreeRight
     *
     * No assertions.
     *
     * @var int
     */
    private $rgt;

    /**
     * @ORM\Column(name="lvl", type="integer")
     * @Gedmo\TreeLevel
     *
     * No assertions.
     *
     * @var int
     */
    private $lvl;

    /**
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @Gedmo\TreeRoot
     *
     * No assertions.
     *
     * @var int
     */
    private $root;

    /**
     * @ORM\ManyToOne(targetEntity="CollectionEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Gedmo\TreeParent
     *
     * No assertions.
     *
     * @var CollectionEntity|null
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CollectionEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * No assertions.
     *
     * @var CollectionEntity[]|ArrayCollection
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity")
     *
     * No assertions.
     *
     * @var AbstractWatermarkEntity|null
     */
    private $watermark;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->hookedObjectCollections = new ArrayCollection();
        $this->defaultTemplate = null;
        $this->permissions = new ArrayCollection();
    }

    /**
     * Converts the entity to an array to be used for JsTree.
     *
     * @param MediaTypeCollection     $mediaTypeCollection
     * @param HookedObjectEntity|null $hookedObjectEntity
     *
     * @return array
     */
    public function toArrayForJsTree(
        MediaTypeCollection $mediaTypeCollection,
        HookedObjectEntity $hookedObjectEntity = null
    ) {
        $children = true;

        $isSelected = false;
        if ($hookedObjectEntity != null) {
            /** @var HookedObjectCollectionEntity $hookedObjectCollectionEntity */
            foreach ($hookedObjectEntity->getHookedObjectCollections() as $hookedObjectCollectionEntity) {
                if ($this->id == $hookedObjectCollectionEntity->getCollection()->getId()) {
                    $isSelected = true;
                    break;
                }
            }
        }

        return [
            'id' => $this->getId(),
            'children' => $children,
            'text' => $this->getTitle(),
            'icon' => 'fa fa-fw fa-picture-o',
            'state' => [
                //'opened' => false, @todo If we set opened to false, already selected collections
                // will not be shown unless the parent is opened.
                'opened' => true,
                'selected' => $isSelected
            ],
            'cmfcmfmediamodule' => $this->toArrayForFinder($mediaTypeCollection)
        ];
    }

    /**
     * Converts the entity to an array to be used with the finder.
     *
     * @param MediaTypeCollection $mediaTypeCollection
     *
     * @return array
     */
    public function toArrayForFinder(MediaTypeCollection $mediaTypeCollection)
    {
        $thumbnail = $this->getMediaForThumbnail();

        $array = [
            'title' => $this->title,
            'slug' => $this->slug,
            'mediaCount' => $this->media->count(),
            'thumbnail' => null
        ];

        if ($thumbnail && $mediaTypeCollection) {
            $array['thumbnail'] = $thumbnail->toArrayForFinder($mediaTypeCollection, false);
        }

        return $array;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return CollectionEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return AbstractMediaEntity|null
     */
    public function getMediaForThumbnail()
    {
        if ($this->media->count() > 0) {
            return $this->media->first();
        }

        return null;
    }

    /**
     * @param RouterInterface $router
     * @param bool            $selfIsClickable Whether or not this collection is clickable.
     *
     * @return array
     */
    public function getBreadcrumbs(RouterInterface $router, $selfIsClickable = false)
    {
        $child = $this;
        $breadcrumbs = [];

        /** @var CollectionEntity $parent */
        while (($parent = $child->getParent()) !== null) {
            $breadcrumbs[] = [
                'url' => $router->generate('cmfcmfmediamodule_collection_display', ['slug' => $parent->getSlug()]),
                'title' => $parent->getTitle()
            ];
            $child = $parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs, false);

        if ($selfIsClickable) {
            $breadcrumbs[] = [
                'url' => $router->generate('cmfcmfmediamodule_collection_display', ['slug' => $this->slug]),
                'title' => $this->title
            ];
        } else {
            $breadcrumbs[] = [
                'url' => null,
                'title' => $this->title
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Whether or not this collection is the root collection.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent === null;
    }

    /**
     * @return CollectionEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param CollectionEntity|null $parent
     */
    public function setParent(CollectionEntity $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return CollectionEntity
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns the title prefixed with multiple "--" depending on the tree level.
     *
     * @return string
     */
    public function getIndentedTitle()
    {
        $indented = '';
        if ($this->lvl > 0) {
            $indented .= '|-';
            if ($this->lvl > 1) {
                $indented .= str_repeat("--", --$this->lvl);
            }
            $indented .= ' ';
        }

        return $indented . $this->title;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return CollectionEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @return CollectionEntity
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
     * @return CollectionEntity
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
     * @return CollectionEntity
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
     * @return CollectionEntity
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * @return AbstractWatermarkEntity|null
     */
    public function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * @param AbstractWatermarkEntity|null $watermark
     *
     * @return CollectionEntity
     */
    public function setWatermark($watermark)
    {
        $this->watermark = $watermark;

        return $this;
    }

    /**
     * @return AbstractMediaEntity[]|ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param AbstractMediaEntity[]|ArrayCollection $media
     *
     * @return CollectionEntity
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return CollectionEntity[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param CollectionEntity[]|ArrayCollection $children
     *
     * @return CollectionEntity
     */
    public function setChildren($children)
    {
        $this->children = $children;

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
     * @return CollectionEntity
     */
    public function setHookedObjectCollections($hookedObjectCollections)
    {
        $this->hookedObjectCollections = $hookedObjectCollections;

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
     * @return CollectionEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * @param string $defaultTemplate
     *
     * @return CollectionEntity
     */
    public function setDefaultTemplate($defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;

        return $this;
    }

    /**
     * @return ArrayCollection|AbstractPermissionEntity[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param AbstractPermissionEntity $permission
     */
    public function addPermission(AbstractPermissionEntity $permission)
    {
        $permission->setCollection($this);
        $this->permissions->add($permission);
    }

    /**
     * @param AbstractPermissionEntity $permission
     */
    public function removePermission(AbstractPermissionEntity $permission)
    {
        $this->permissions->removeElement($permission);
    }

    /**
     * Get page category assignments
     *
     * @return ArrayCollection|CollectionCategoryAssignmentEntity[]
     */
    public function getCategoryAssignments()
    {
        return $this->categoryAssignments;
    }

    /**
     * Set page category assignments
     *
     * @param ArrayCollection $assignments
     */
    public function setCategoryAssignments(ArrayCollection $assignments)
    {
        foreach ($this->categoryAssignments as $categoryAssignment) {
            if (false === $key = $this->collectionContains($assignments, $categoryAssignment)) {
                $this->categoryAssignments->removeElement($categoryAssignment);
            } else {
                $assignments->remove($key);
            }
        }
        foreach ($assignments as $assignment) {
            $this->categoryAssignments->add($assignment);
        }
    }

    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, category).
     * @param ArrayCollection $collection
     * @param CollectionCategoryAssignmentEntity $element
     * @return bool|int
     */
    private function collectionContains(ArrayCollection $collection, CollectionCategoryAssignmentEntity $element)
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var CollectionCategoryAssignmentEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {

                return $key;
            }
        }

        return false;
    }
}
