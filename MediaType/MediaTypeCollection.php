<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;

class MediaTypeCollection
{
    /**
     * @var array|MediaTypeInterface[]
     */
    private $mediaTypes;

    public function __construct()
    {
        $this->mediaTypes = [];
    }

    public function addMediaType(MediaTypeInterface $mediaType)
    {
        $this->mediaTypes[$mediaType->getAlias()] = $mediaType;
    }

    /**
     * @return array|MediaTypeInterface[]
     */
    public function getMediaTypes()
    {
        return $this->mediaTypes;
    }

    /**
     * @return array|MediaTypeInterface[]|UploadableMediaTypeInterface[]
     */
    public function getUploadableMediaTypes()
    {
        return array_filter($this->mediaTypes, function (MediaTypeInterface $mediaType) {
            return $mediaType instanceof UploadableMediaTypeInterface;
        });
    }

    /**
     * @param bool $onlyEnabled
     * @return array|MediaTypeInterface[]|WebMediaTypeInterface[]
     */
    public function getWebMediaTypes($onlyEnabled = false)
    {
        return array_filter($this->mediaTypes, function (MediaTypeInterface $mediaType) use ($onlyEnabled) {
            return (!$onlyEnabled ||$mediaType->isEnabled()) && $mediaType instanceof WebMediaTypeInterface;
        });
    }

    /**
     * @return array|MediaTypeInterface[]|PasteMediaTypeInterface[]
     */
    public function getPasteMediaTypes()
    {
        return array_filter($this->mediaTypes, function (MediaTypeInterface $mediaType) {
            return $mediaType instanceof PasteMediaTypeInterface;
        });
    }

    public function getMediaType($mediaType)
    {
        if (!isset($this->mediaTypes[$mediaType])) {
            throw new \InvalidArgumentException(sprintf('Media type %s does not exist!', $mediaType));
        }
        return $this->mediaTypes[$mediaType];
    }

    /**
     * @param AbstractMediaEntity $entity
     * @return MediaTypeInterface
     */
    public function getMediaTypeFromEntity(AbstractMediaEntity $entity)
    {
        $class = get_class($entity);
        $class = explode('\\', $class);
        $class = $class[count($class) - 1];
        $class = substr($class, 0, -strlen('Entity'));

        return $this->getMediaType(lcfirst($class));
    }
}
