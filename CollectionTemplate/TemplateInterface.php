<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
