<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Cmfcmf\Module\MediaModule\Security\SecurityGraph;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Generates the permission graph.
 */
class CollectionPermissionSecurityTree
{
    /**
     * Disallow any access.
     */
    const PERM_LEVEL_NONE = 'none';

    /**
     * Only allow basic overview access. A single medium is not accessable.
     */
    const PERM_LEVEL_OVERVIEW = 'overview';

    /**
     * Allows to download a whole collection.
     */
    const PERM_LEVEL_DOWNLOAD_COLLECTION = 'download-collection';

    /**
     * Allows to view media details.
     */
    const PERM_LEVEL_MEDIA_DETAILS = 'media-details';

    /**
     * Allows to download a single medium.
     */
    const PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM = 'download-single-media';

    /**
     * Allows to add media to the collection.
     */
    const PERM_LEVEL_ADD_MEDIA = 'add-media';

    /**
     * Allows to create new sub-collections.
     */
    const PERM_LEVEL_ADD_SUB_COLLECTIONS = 'add-sub-collections';

    /**
     * Allows to edit media.
     */
    const PERM_LEVEL_EDIT_MEDIA = 'edit-media';

    /**
     * Allows to edit the collection.
     */
    const PERM_LEVEL_EDIT_COLLECTION = 'edit-collection';

    /**
     * Allows to delete media.
     */
    const PERM_LEVEL_DELETE_MEDIA = 'delete-media';

    /**
     * Allows to delete the collection.
     */
    const PERM_LEVEL_DELETE_COLLECTION = 'delete-collection';

    /**
     * Allows to enhance permissions by adding permissions with goOn = 1.
     */
    const PERM_LEVEL_ENHANCE_PERMISSIONS = 'enhance-permissions';

    /**
     * Allows to change permissions.
     */
    const PERM_LEVEL_CHANGE_PERMISSIONS = 'change-permissions';

    /**
     * Permission A requires permission B.
     */
    const EDGE_TYPE_REQUIRES = 'requires';

    /**
     * Permission A conflicts with permission B.
     */
    const EDGE_TYPE_CONFLICTS = 'conflicts';

    /**
     * Creates the permission graph.
     *
     * @param TranslatorInterface $translator
     * @param 'cmfcmfmediamodule'
     *
     * @return SecurityGraph
     */
    public static function createGraph(TranslatorInterface $translator)
    {
        $categories = self::getCategories($translator);

        $graph = new SecurityGraph();
        /** @var Vertex[] $vertices */
        $vertices = [];

        // NO ACCESS
        $vertex = $graph->createVertex(self::PERM_LEVEL_NONE);
        $vertex->setAttribute('title', $translator->trans('No access', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Revokes all access.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['no-access']);
        $vertex->setGroup($categories['no-access']->getId());
        $vertices[self::PERM_LEVEL_NONE] = $vertex;

        // VIEW
        $vertex = $graph->createVertex(self::PERM_LEVEL_OVERVIEW);
        $vertex->setAttribute('title', $translator->trans('Sub-collection and media overview', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute(
            'description',
            $translator->trans('Grants access to view the collection\'s media and sub-collections.', [], 'cmfcmfmediamodule')
        );
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertices[self::PERM_LEVEL_OVERVIEW] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DOWNLOAD_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Download whole collection', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to download the whole collection.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_OVERVIEW])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_MEDIA_DETAILS);
        $vertex->setAttribute('title', $translator->trans('Display media details', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to the media details page.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_OVERVIEW])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_MEDIA_DETAILS] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM);
        $vertex->setAttribute('title', $translator->trans('Download single media', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to download a single medium.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_MEDIA_DETAILS])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM] = $vertex;

        // ADD
        $vertex = $graph->createVertex(self::PERM_LEVEL_ADD_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Add new media', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Grants access to create new media.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['add']);
        $vertex->setGroup($categories['add']->getId());
        $vertices[self::PERM_LEVEL_ADD_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_ADD_SUB_COLLECTIONS);
        $vertex->setAttribute('title', $translator->trans('Add new sub-collections', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to create new collections.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['add']);
        $vertex->setGroup($categories['add']->getId());
        $vertices[self::PERM_LEVEL_ADD_SUB_COLLECTIONS] = $vertex;

        // EDIT
        $vertex = $graph->createVertex(self::PERM_LEVEL_EDIT_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Edit media', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Grants access to edit media.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['edit']);
        $vertex->setGroup($categories['edit']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_ADD_MEDIA])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_EDIT_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_EDIT_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Edit collection', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Grants access to edit the collection.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['edit']);
        $vertex->setGroup($categories['edit']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_EDIT_COLLECTION] = $vertex;

        // DELETE
        $vertex = $graph->createVertex(self::PERM_LEVEL_DELETE_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Delete media', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Grants access to delete media.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['delete']);
        $vertex->setGroup($categories['delete']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_EDIT_MEDIA])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DELETE_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DELETE_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Delete collection', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to delete the collection.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['delete']);
        $vertex->setGroup($categories['delete']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DELETE_MEDIA])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_EDIT_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_ADD_SUB_COLLECTIONS])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DELETE_COLLECTION] = $vertex;

        // PERMISSIONS
        $vertex = $graph->createVertex(self::PERM_LEVEL_ENHANCE_PERMISSIONS);
        $vertex->setAttribute('title', $translator->trans('Enhance permissions', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Allows to add permissions.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['permission']);
        $vertex->setGroup($categories['permission']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DELETE_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_ENHANCE_PERMISSIONS] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_CHANGE_PERMISSIONS);
        $vertex->setAttribute('title', $translator->trans('Change permissions', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('description', $translator->trans('Allows to adjust the permissions.', [], 'cmfcmfmediamodule'));
        $vertex->setAttribute('category', $categories['permission']);
        $vertex->setGroup($categories['permission']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_ENHANCE_PERMISSIONS])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_CHANGE_PERMISSIONS] = $vertex;

        foreach ($vertices as $permissionLevel => $vertex) {
            if ($permissionLevel == self::PERM_LEVEL_NONE) {
                continue;
            }
            $vertex->createEdge($vertices[self::PERM_LEVEL_NONE])
                ->setAttribute('edgeType', self::EDGE_TYPE_CONFLICTS);
        }

        return $graph;
    }

    /**
     * Get a list of permission categories.
     *
     * @param TranslatorInterface $translator
     * @param 'cmfcmfmediamodule'
     *
     * @return CollectionPermissionCategory[]
     */
    public static function getCategories(TranslatorInterface $translator)
    {
        return [
            'no-access' => new CollectionPermissionCategory(0, $translator->trans('No access', [], 'cmfcmfmediamodule')),
            'view' => new CollectionPermissionCategory(1, $translator->trans('View', [], 'cmfcmfmediamodule')),
            'add' => new CollectionPermissionCategory(2, $translator->trans('Add', [], 'cmfcmfmediamodule')),
            'edit' => new CollectionPermissionCategory(3, $translator->trans('Edit', [], 'cmfcmfmediamodule')),
            'delete' => new CollectionPermissionCategory(4, $translator->trans('Delete', [], 'cmfcmfmediamodule')),
            'permission' => new CollectionPermissionCategory(5, $translator->trans('Permissions', [], 'cmfcmfmediamodule')),
        ];
    }
}
