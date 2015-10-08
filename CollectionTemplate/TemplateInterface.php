<?php

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Symfony\Component\HttpFoundation\Response;

interface TemplateInterface
{
    /**
     * @param CollectionEntity    $collectionEntity
     * @param MediaTypeCollection $mediaTypeCollection
     * @param $showChildCollections
     *
     * @return Response
     */
    public function render(CollectionEntity $collectionEntity, MediaTypeCollection $mediaTypeCollection, $showChildCollections);

    public function getName();

    public function getTitle();
}
