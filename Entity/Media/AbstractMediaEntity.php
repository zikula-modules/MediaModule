<?php

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
     **/
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

    /**
     * @param RequestStack $requestStack
     * @param string       $dataDirectory
     */
    public function __construct(RequestStack $requestStack, $dataDirectory = '')
    {
        // Position at the end of the album.
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
    public function makeNonPrimaryOnDelete()
    {
        $primaryMedium = $this->collection->getPrimaryMedium();
        if ($primaryMedium && $primaryMedium->getId() == $this->id) {
            $this->collection->setPrimaryMedium(null);
        }
    }

    public function getImagineId()
    {
        return 'media-' . $this->id;
    }

    public function getAttribution($format = 'html')
    {
        if (null === $this->author && null === $this->authorUrl) {
            return null;
        }

        if ('html' == $format) {
            if (null === $this->authorUrl) {
                $author = htmlentities($this->author);
            } elseif (null === $this->author) {
                $author = '<a href="' . htmlentities($this->authorUrl). '">' . htmlentities($this->authorUrl) . '</a>';
            } else {
                $author = '<a href="' . htmlentities($this->authorUrl). '">' . htmlentities($this->author) . '</a>';
            }
        } elseif ('raw' == $format) {
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

    /**
     * @return CollectionEntity
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param CollectionEntity $collection
     *
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * Set license.
     *
     * @param LicenseEntity $license
     *
     * @return $this
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license.
     *
     * @return LicenseEntity|null
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorAvatarUrl()
    {
        return $this->authorAvatarUrl;
    }

    /**
     * @param string $authorAvatarUrl
     *
     * @return $this
     */
    public function setAuthorAvatarUrl($authorAvatarUrl)
    {
        $this->authorAvatarUrl = $authorAvatarUrl;

        return $this;
    }

    /**
     * @param array $extraData
     *
     * @return $this
     */
    public function setExtraData($extraData)
    {
        $this->extraData = $extraData;

        return $this;
    }

    /**
     * @param array $extraData
     *
     * @return $this
     */
    public function addExtraData($extraData)
    {
        $this->extraData = array_merge($this->extraData, $extraData);

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraData()
    {
        return $this->extraData;
    }

    public function toArrayForFinder(MediaTypeCollection $mediaTypeCollection, $includeCollection = true)
    {
        $class = get_class($this);
        $type = substr($class, strrpos($class, '\\') + 1, -strlen('Entity'));
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($this);

        return [
            'id' => $this->id,
            'type' => $type,
            'title' => $this->title,
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
     * @param HookedObjectEntity[]|ArrayCollection $hookedObjectMedia
     *
     * @return AbstractMediaEntity
     */
    public function setHookedObjectMedia($hookedObjectMedia)
    {
        $this->hookedObjectMedia = $hookedObjectMedia;

        return $this;
    }

    /**
     * @return HookedObjectEntity[]|ArrayCollection
     */
    public function getHookedObjectMedia()
    {
        return $this->hookedObjectMedia;
    }

    /**
     * @param string $authorUrl
     *
     * @return $this
     */
    public function setAuthorUrl($authorUrl)
    {
        $this->authorUrl = $authorUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->authorUrl;
    }

    /**
     * @param int $version
     *
     * @return AbstractMediaEntity
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
     *
     * @param ArrayCollection               $collection
     * @param MediaCategoryAssignmentEntity $element
     *
     * @return bool|int
     */
    private function collectionContains(ArrayCollection $collection, MediaCategoryAssignmentEntity $element)
    {
        foreach ($collection as $key => $collectionAssignment) {
            /** @var MediaCategoryAssignmentEntity $collectionAssignment */
            if ($collectionAssignment->getCategoryRegistryId() == $element->getCategoryRegistryId()
                && $collectionAssignment->getCategory() == $element->getCategory()
            ) {
                return $key;
            }
        }

        return false;
    }

    /**
     * @param int $views
     *
     * @return AbstractMediaEntity
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @return int
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param int $downloads
     *
     * @return $this
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;

        return $this;
    }

    protected function getBaseUri()
    {
        return $this->requestStack->getCurrentRequest()->getBasePath();
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return AbstractWatermarkEntity
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @param string $dataDirectory
     *
     * @return AbstractWatermarkEntity
     */
    public function setDataDirectory($dataDirectory)
    {
        $this->dataDirectory = $dataDirectory;

        return $this;
    }
}
