<?php

namespace Cmfcmf\Module\MediaModule\Entity\Collection;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Sluggable;
use Gedmo\Tree\Node;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;

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
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     *
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"title"}, handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\TreeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="parentRelationField", value="parent"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="/")
     *      })
     * })
     * @ORM\Column(length=128, unique=true)
     * 
     * @var string
     */
    protected $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    protected $defaultTemplate;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity", mappedBy="collection", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var AbstractMediaEntity[]|ArrayCollection
     **/
    protected $media;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity", mappedBy="collection", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @var HookedObjectCollectionEntity[]|ArrayCollection
     */
    protected $hookedObjectCollections;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     *
     * @var int
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     *
     * @var int
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     *
     * @var int
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     *
     * @var int
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="CollectionEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var CollectionEntity|null
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CollectionEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * @var CollectionEntity[]|ArrayCollection
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity")
     *
     * @var AbstractWatermarkEntity|null
     **/
    private $watermark;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     *
     * @var int
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     *
     * @var int
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $updatedDate;

    /**
     * Whether or not this is the virtual root collection.
     *
     * @var bool
     */
    private $virtualRoot;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->hookedObjectCollections = new ArrayCollection();
        $this->defaultTemplate = null;
    }

    public function toArrayForFinder(MediaTypeCollection $mediaTypeCollection)
    {
        $thumbnail = $this->getMediaForThumbnail();

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'mediaCount' => $this->media->count(),
            'thumbnail' => $thumbnail && $mediaTypeCollection ? $thumbnail->toArrayForFinder($mediaTypeCollection, false) : null
        ];
    }

    public function toArrayForJsTree(MediaTypeCollection $mediaTypeCollection, HookedObjectEntity $hookedObjectEntity = null, $includeChildren)
    {
        $children = true;
        if ($includeChildren) {
            $children = [];
            foreach ($this->children as $child) {
                $children[] = $child->toArrayForJsTree($mediaTypeCollection, $hookedObjectEntity, true);
            }
        }

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
                'opened' => true,
                'selected' => $isSelected
            ],
            'cmfcmfmediamodule' => $this->toArrayForFinder($mediaTypeCollection)
        ];
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

        if (!$this->virtualRoot) {
            $breadcrumbs[] = [
                'url' => $router->generate('cmfcmfmediamodule_collection_displayroot'),
                'title' => 'Root collections'
            ];
        }

        $breadcrumbs = array_reverse($breadcrumbs, false);

        $breadcrumbs[] = [
            'url' => $selfIsClickable ? $router->generate('cmfcmfmediamodule_collection_display', ['slug' => $this->slug]) : null,
            'title' => $this->title
        ];

        return $breadcrumbs;
    }

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     *
     * @return CollectionEntity
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
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
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    public function setParent(CollectionEntity $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return CollectionEntity
     */
    public function getParent()
    {
        return $this->parent;
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
     * @return AbstractWatermarkEntity|null
     */
    public function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * @param \Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity[]|ArrayCollection $media
     *
     * @return CollectionEntity
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return \Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity[]|ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
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
     * @return HookedObjectCollectionEntity[]|ArrayCollection
     */
    public function getHookedObjectCollections()
    {
        return $this->hookedObjectCollections;
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param bool $virtualRoot
     *
     * @return CollectionEntity
     */
    public function setVirtualRoot($virtualRoot)
    {
        $this->virtualRoot = $virtualRoot;
    }

    /**
     * @return bool
     */
    public function isVirtualRoot()
    {
        return $this->virtualRoot;
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
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }
}
