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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Traits\StandardFieldsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Sluggable;
use Gedmo\Sortable\Sortable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\Media\Repository\MediaRepository")
 * @ORM\Table(name="cmfcmfmedia_media")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr")
 * @ORM\DiscriminatorMap({
 *  "deezer"         = "DeezerEntity",
 *  "soundcloud"     = "SoundCloudEntity",
 *  "twitter"        = "TwitterEntity",
 *  "flickr"         = "FlickrEntity",
 *  "youtube"        = "YouTubeEntity",
 *  "url"            = "UrlEntity",
 *  "image"          = "ImageEntity",
 *  "pdf"            = "PdfEntity",
 *  "plaintext"      = "PlaintextEntity",
 *  "markdown"       = "MarkdownEntity",
 *  "archive"        = "ArchiveEntity",
 *  "file"           = "AbstractFileEntity",
 *  "video"          = "VideoEntity",
 *  "audio"          = "AudioEntity",
 *  "unknown"        = "UnknownEntity",
 * })
 */
abstract class AbstractMediaEntity implements Sluggable, Sortable
{
    use StandardFieldsTrait;

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
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"title"}, unique_base="collection", unique=true, updatable=true, handlers={
     *      @Gedmo\SlugHandler(class="Cmfcmf\Module\MediaModule\Helper\MediaSlugHandler", options={})
     * })
     * @ORM\Column(length=128)
     */
    protected $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
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
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255")
     *
     * @var string
     */
    protected $author;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255")
     * @Assert\Url()
     *
     * @var string
     */
    protected $authorUrl;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Assert\Length(max="255")
     * @Assert\Url()
     *
     * @var string
     */
    protected $authorAvatarUrl;

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    protected $extraData;

    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity", fetch="EAGER")
     *
     * @var LicenseEntity|null
     */
    protected $license;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", inversedBy="media")
     *
     * @var CollectionEntity
     */
    protected $collection;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectMediaEntity", mappedBy="media", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @var HookedObjectMediaEntity[]|ArrayCollection
     */
    protected $hookedObjectMedia;

    /**
     * @ORM\OneToMany(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\MediaCategoryAssignmentEntity",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     *
     * @var ArrayCollection|MediaCategoryAssignmentEntity[]
     */
    private $categoryAssignments;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $dataDirectory;

    public function __construct(RequestStack $requestStack, string $dataDirectory = '')
    {
        // position at the end of the album
        $this->position = -1;
        $this->extraData = [];
        $this->views = 0;
        $this->downloads = 0;
        $this->hookedObjectMedia = new ArrayCollection();
        $this->categoryAssignments = new ArrayCollection();

        // TODO refactor these dependencies out of the entities
        $this->requestStack = $requestStack;
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * @ORM\PreRemove()
     */
    public function makeNonPrimaryOnDelete(): void
    {
        $primaryMedium = $this->collection->getPrimaryMedium();
        if ($primaryMedium && $primaryMedium->getId() === $this->id) {
            $this->collection->setPrimaryMedium(null);
        }
    }

    public function getImagineId(): string
    {
        return 'media-' . $this->id;
    }

    public function getAttribution($format = 'html'): ?string
    {
        if (null === $this->author && null === $this->authorUrl) {
            return null;
        }

        if ('html' === $format) {
            if (null === $this->authorUrl) {
                $author = htmlentities($this->author);
            } elseif (null === $this->author) {
                $author = '<a href="' . htmlentities($this->authorUrl) . '">' . htmlentities($this->authorUrl) . '</a>';
            } else {
                $author = '<a href="' . htmlentities($this->authorUrl) . '">' . htmlentities($this->author) . '</a>';
            }
        } elseif ('raw' === $format) {
            $author = '';
            if (null !== $this->author) {
                $author .= $this->author . ' ';
            }
            if (null !== $this->authorUrl) {
                $author .= '(' . $this->authorUrl . ')';
            }
            $author = trim($author);
        }

        return $author;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getLicense(): ?LicenseEntity
    {
        return $this->license;
    }

    public function setLicense(LicenseEntity $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorAvatarUrl(): ?string
    {
        return $this->authorAvatarUrl;
    }

    public function setAuthorAvatarUrl(?string $authorAvatarUrl): self
    {
        $this->authorAvatarUrl = $authorAvatarUrl;

        return $this;
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;

        return $this;
    }

    public function addExtraData(array $extraData): self
    {
        $this->extraData = array_merge($this->extraData, $extraData);

        return $this;
    }

    public function toArrayForFinder(
        MediaTypeCollection $mediaTypeCollection,
        bool $includeCollection = true
    ): array {
        $class = static::class;
        $type = mb_substr($class, mb_strrpos($class, '\\') + 1, -mb_strlen('Entity'));
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($this);

        $preview = '';
        if ('Image' === $type) {
            $preview = $mediaType->renderFullpage($this);
        }

        return [
            'id' => $this->id,
            'type' => $type,
            'title' => $this->title,
            'preview' => $preview,
            'slug' => $this->slug,
            'description' => $this->description,
            'license' => null !== $this->getLicense() ? $this->getLicense()->toArray() : null,
            'embedCodes' => [
                'full' => $mediaType->getEmbedCode($this),
                'medium' => $mediaType->getEmbedCode($this, 'medium'),
                'small' => $mediaType->getEmbedCode($this, 'small')
            ],
            'thumbnail' => [
                'small' => $mediaType->getThumbnail($this, 200, 150, 'url')
            ],
            'collection' => $includeCollection ? $this->getCollection()->toArrayForFinder($mediaTypeCollection) : null
        ];
    }

    /**
     * @return HookedObjectEntity[]|ArrayCollection
     */
    public function getHookedObjectMedia()
    {
        return $this->hookedObjectMedia;
    }

    /**
     * @param HookedObjectEntity[]|ArrayCollection $hookedObjectMedia
     */
    public function setHookedObjectMedia($hookedObjectMedia): self
    {
        $this->hookedObjectMedia = $hookedObjectMedia;

        return $this;
    }

    public function getAuthorUrl(): ?string
    {
        return $this->authorUrl;
    }

    public function setAuthorUrl(string $authorUrl): self
    {
        $this->authorUrl = $authorUrl;

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

    /**
     * Get page category assignments.
     *
     * @return ArrayCollection|MediaCategoryAssignmentEntity[]
     */
    public function getCategoryAssignments()
    {
        return $this->categoryAssignments;
    }

    /**
     * Set page category assignments.
     */
    public function setCategoryAssignments(ArrayCollection $assignments): self
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

        return $this;
    }

    /**
     * Check if a collection contains an element based only on two criteria (categoryRegistryId, category).
     *
     * @return bool|int
     */
    private function collectionContains(
        ArrayCollection $collection,
        MediaCategoryAssignmentEntity $element
    ) {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var MediaCategoryAssignmentEntity $collectionAssignment */
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

    protected function getBaseUri(): string
    {
        return $this->requestStack->getCurrentRequest()->getBasePath();
    }

    public function setRequestStack(RequestStack $requestStack): self
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    public function setDataDirectory(string $dataDirectory): self
    {
        $this->dataDirectory = $dataDirectory;

        return $this;
    }
}
