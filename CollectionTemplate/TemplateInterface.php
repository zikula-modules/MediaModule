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
     * Renders the template with the given collection.
     *
     * @param CollectionEntity    $collectionEntity     The collection to render.
     * @param MediaTypeCollection $mediaTypeCollection  A collection of media types.
     * @param bool                $showChildCollections Whether or not to show child collections.
     *
     * @return Response
     */
    public function render(
        CollectionEntity $collectionEntity,
        MediaTypeCollection $mediaTypeCollection,
        $showChildCollections
    );

    /**
     * A unique name which is used to refer to the template in the database.
     * It is best practice to prefix it with your module's name.
     *
     * @return string
     */
    public function getName();

    /**
     * The title of the collection template. It is shown when the user selects the template
     * to use for a collection.
     *
     * @return string
     */
    public function getTitle();
}
