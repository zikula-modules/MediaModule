<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Fhaculty\Graph\Vertex;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityTree
{
    const PERM_LEVEL_NONE = 'none';

    const PERM_LEVEL_OVERVIEW = 'overview';

    const PERM_LEVEL_DOWNLOAD_COLLECTION = 'download-collection';

    const PERM_LEVEL_MEDIA_DETAILS = 'media-details';

    const PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM = 'download-single-media';

    const PERM_LEVEL_ADD_MEDIA = 'add-media';

    const PERM_LEVEL_ADD_SUB_COLLECTIONS = 'add-sub-collections';

    const PERM_LEVEL_EDIT_MEDIA = 'edit-media';

    const PERM_LEVEL_EDIT_COLLECTION = 'edit-collection';

    const PERM_LEVEL_DELETE_MEDIA = 'delete-media';

    const PERM_LEVEL_DELETE_COLLECTION = 'delete-collection';

    const PERM_LEVEL_ADD_PERMISSIONS = 'add-permissions';

    const PERM_LEVEL_CHANGE_PERMISSIONS = 'change-permissions';

    const EDGE_TYPE_REQUIRES = 'requires';

    const EDGE_TYPE_CONFLICTS = 'conflicts';

    /**
     * @param TranslatorInterface $translator
     * @param $domain
     *
     * @return SecurityGraph
     */
    public static function createGraph(TranslatorInterface $translator, $domain)
    {
        $categories = self::getCategories($translator, $domain);

        require_once __DIR__ . '/../vendor/autoload.php';

        $graph = new SecurityGraph();
        /** @var Vertex[] $vertices */
        $vertices = [];

        // NO ACCESS
        $vertex = $graph->createVertex(self::PERM_LEVEL_NONE);
        $vertex->setAttribute('title', $translator->trans('No access', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Revokes all access.', [], $domain));
        $vertex->setAttribute('category', $categories['no-access']);
        $vertex->setGroup($categories['no-access']->getId());
        $vertices[self::PERM_LEVEL_NONE] = $vertex;

        // VIEW
        $vertex = $graph->createVertex(self::PERM_LEVEL_OVERVIEW);
        $vertex->setAttribute('title', $translator->trans('Sub-collection and media overview', [], $domain));
        $vertex->setAttribute(
            'description',
            $translator->trans('Grants access to view the collection\'s media and sub-collections.', [], $domain)
        );
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertices[self::PERM_LEVEL_OVERVIEW] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DOWNLOAD_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Download whole collection', [], $domain));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to download the whole collection.', [], $domain));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_OVERVIEW])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_MEDIA_DETAILS);
        $vertex->setAttribute('title', $translator->trans('Display media details', [], $domain));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to the media details page.', [], $domain));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_OVERVIEW])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_MEDIA_DETAILS] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM);
        $vertex->setAttribute('title', $translator->trans('Download single media', [], $domain));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to download a single medium.', [], $domain));
        $vertex->setAttribute('category', $categories['view']);
        $vertex->setGroup($categories['view']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_MEDIA_DETAILS])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM] = $vertex;

        // ADD
        $vertex = $graph->createVertex(self::PERM_LEVEL_ADD_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Add new media', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Grants access to create new media.', [], $domain));
        $vertex->setAttribute('category', $categories['add']);
        $vertex->setGroup($categories['add']->getId());
        $vertices[self::PERM_LEVEL_ADD_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_ADD_SUB_COLLECTIONS);
        $vertex->setAttribute('title', $translator->trans('Add new sub-collections', [], $domain));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to create new collections.', [], $domain));
        $vertex->setAttribute('category', $categories['add']);
        $vertex->setGroup($categories['add']->getId());
        $vertices[self::PERM_LEVEL_ADD_SUB_COLLECTIONS] = $vertex;

        // EDIT
        $vertex = $graph->createVertex(self::PERM_LEVEL_EDIT_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Edit media', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Grants access to edit media.', [], $domain));
        $vertex->setAttribute('category', $categories['edit']);
        $vertex->setGroup($categories['edit']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_ADD_MEDIA])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_EDIT_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_EDIT_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Edit collection', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Grants access to edit the collection.', [], $domain));
        $vertex->setAttribute('category', $categories['edit']);
        $vertex->setGroup($categories['edit']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DOWNLOAD_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_EDIT_COLLECTION] = $vertex;

        // DELETE
        $vertex = $graph->createVertex(self::PERM_LEVEL_DELETE_MEDIA);
        $vertex->setAttribute('title', $translator->trans('Delete media', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Grants access to delete media.', [], $domain));
        $vertex->setAttribute('category', $categories['delete']);
        $vertex->setGroup($categories['delete']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_EDIT_MEDIA])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_DELETE_MEDIA] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_DELETE_COLLECTION);
        $vertex->setAttribute('title', $translator->trans('Delete collection', [], $domain));
        $vertex->setAttribute('description',
            $translator->trans('Grants access to delete the collection.', [], $domain));
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
        $vertex = $graph->createVertex(self::PERM_LEVEL_ADD_PERMISSIONS);
        $vertex->setAttribute('title', $translator->trans('Add permissions', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Allows to add permissions.', [], $domain));
        $vertex->setAttribute('category', $categories['permission']);
        $vertex->setGroup($categories['permission']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_DELETE_COLLECTION])
            ->setAttribute('edgeType', self::EDGE_TYPE_REQUIRES);
        $vertices[self::PERM_LEVEL_ADD_PERMISSIONS] = $vertex;

        $vertex = $graph->createVertex(self::PERM_LEVEL_CHANGE_PERMISSIONS);
        $vertex->setAttribute('title', $translator->trans('Change permissions', [], $domain));
        $vertex->setAttribute('description', $translator->trans('Allows to adjust the permissions.', [], $domain));
        $vertex->setAttribute('category', $categories['permission']);
        $vertex->setGroup($categories['permission']->getId());
        $vertex->createEdgeTo($vertices[self::PERM_LEVEL_ADD_PERMISSIONS])
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
     * @param TranslatorInterface $translator
     * @param $domain
     *
     * @return CollectionPermissionCategory[]
     */
    public static function getCategories(TranslatorInterface $translator, $domain)
    {
        return [
            'no-access' => new CollectionPermissionCategory(0, $translator->trans('No access', [], $domain)),
            'view' => new CollectionPermissionCategory(1, $translator->trans('View', [], $domain)),
            'add' => new CollectionPermissionCategory(2, $translator->trans('Add', [], $domain)),
            'edit' => new CollectionPermissionCategory(3, $translator->trans('Edit', [], $domain)),
            'delete' => new CollectionPermissionCategory(4, $translator->trans('Delete', [], $domain)),
            'permission' => new CollectionPermissionCategory(5, $translator->trans('Permissions', [], $domain)),
        ];
    }
}
