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

namespace Cmfcmf\Module\MediaModule\Entity\Collection;

use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectCollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Traits\StandardFieldsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
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
    use StandardFieldsTrait;

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
     * @ORM\Column(type="integer")
     *
     * No assertions.
     *
     * @var int
     */
    protected $views;

    /**
     * @ORM\Column(type="integer")
     *
     * No assertions.
     *
     * @var int
     */
    protected $downloads;

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
     */
    protected $media;

    /**
     * @ORM\OneToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity", fetch="EAGER")
     *
     * No assertions.
     *
     * @var AbstractMediaEntity
     */
    protected $primaryMedium;

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
        $this->permissions = new ArrayCollection();
        $this->categoryAssignments = new ArrayCollection();
        $this->defaultTemplate = null;
        $this->views = 0;
        $this->downloads = 0;
    }

    /**
     * @Assert\IsTrue(message="The selected primary medium is not part of the collection!")
     */
    public function isPrimaryMediumInMediaCollection(): bool
    {
        return null === $this->primaryMedium || $this->media->contains($this->primaryMedium);
    }

    /**
     * Converts the entity to an array to be used for JsTree.
     */
    public function toArrayForJsTree(
        MediaTypeCollection $mediaTypeCollection,
        HookedObjectEntity $hookedObjectEntity = null
    ): array {
        $children = true;

        $isSelected = false;
        if (null !== $hookedObjectEntity) {
            /** @var HookedObjectCollectionEntity $hookedObjectCollectionEntity */
            foreach ($hookedObjectEntity->getHookedObjectCollections() as $hookedObjectCollectionEntity) {
                if ($this->id === $hookedObjectCollectionEntity->getCollection()->getId()) {
                    $isSelected = true;
                    break;
                }
            }
        }

        return [
            'id' => $this->getId(),
            'children' => $children,
            'text' => $this->getTitle(),
            'icon' => 'fas fa-fw fa-image',
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
     */
    public function toArrayForFinder(MediaTypeCollection $mediaTypeCollection): array
    {
        $thumbnail = $this->getPrimaryMedium(true);

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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBreadcrumbs(RouterInterface $router, bool $selfIsClickable = false): array
    {
        $child = $this;
        $breadcrumbs = [];

        /** @var CollectionEntity $parent */
        while (null !== ($parent = $child->getParent())) {
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
     */
    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Returns the title prefixed with multiple "--" depending on the tree level.
     */
    public function getIndentedTitle(): string
    {
        $indented = '';
        if ($this->lvl > 0) {
            $indented .= '|-';
            if (1 < $this->lvl) {
                $indented .= str_repeat('--', --$this->lvl);
            }
            $indented .= ' ';
        }

        return $indented . $this->title;
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

    public function getWatermark(): ?AbstractWatermarkEntity
    {
        return $this->watermark;
    }

    public function setWatermark(?AbstractWatermarkEntity $watermark = null): self
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
     */
    public function setMedia($media): self
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
     */
    public function setChildren($children): self
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
     */
    public function setHookedObjectCollections($hookedObjectCollections): self
    {
        $this->hookedObjectCollections = $hookedObjectCollections;

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

    public function getDefaultTemplate(): ?string
    {
        return $this->defaultTemplate;
    }

    public function setDefaultTemplate(string $defaultTemplate): self
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

    public function addPermission(AbstractPermissionEntity $permission): void
    {
        $permission->setCollection($this);
        $this->permissions->add($permission);
    }

    public function removePermission(AbstractPermissionEntity $permission): void
    {
        $this->permissions->removeElement($permission);
    }

    /**
     * Get page category assignments.
     *
     * @return ArrayCollection|CollectionCategoryAssignmentEntity[]
     */
    public function getCategoryAssignments()
    {
        return $this->categoryAssignments;
    }

    /**
     * Set page category assignments.
     */
    public function setCategoryAssignments(ArrayCollection $assignments): void
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
     *
     * @return bool|int
     */
    private function collectionContains(
        ArrayCollection $collection,
        CollectionCategoryAssignmentEntity $element
    ) {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var CollectionCategoryAssignmentEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() === $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() === $element->getCategory()
            ) {
                return $key;
            }
        }

        return false;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getDownloads(): ?int
    {
        return $this->downloads;
    }

    public function setDownloads(int $downloads): self
    {
        $this->downloads = $downloads;

        return $this;
    }

    public function setPrimaryMedium(AbstractMediaEntity $primaryMedium): self
    {
        $this->primaryMedium = $primaryMedium;

        return $this;
    }

    public function getPrimaryMedium(bool $useFirstIfNoneSpecified = false): ?AbstractMediaEntity
    {
        if ($useFirstIfNoneSpecified && null === $this->primaryMedium && !$this->media->isEmpty()) {
            return $this->media->first();
        }

        return $this->primaryMedium;
    }
}
