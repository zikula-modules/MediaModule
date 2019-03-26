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

namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

interface TemplateInterface
{
    /**
     * Renders the template with the given collection.
     *
     * @param CollectionEntity    $collectionEntity     the collection to render
     * @param MediaTypeCollection $mediaTypeCollection  a collection of media types
     * @param bool                $showChildCollections whether or not to show child collections
     * @param array               $options              collection template specific option array
     *
     * @return Response
     */
    public function render(
        CollectionEntity $collectionEntity,
        MediaTypeCollection $mediaTypeCollection,
        $showChildCollections,
        array $options
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

    /**
     * A settings form with additional settings. It will be displayed if the template is selected.
     *
     * @return string|FormInterface|null
     */
    public function getSettingsForm();

    /**
     * Default form options.
     *
     * @return array
     */
    public function getDefaultOptions();
}
