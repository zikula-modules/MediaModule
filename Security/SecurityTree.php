<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Fhaculty\Graph\Graph;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityTree
{
    /**
     * @param TranslatorInterface $translator
     * @param $domain
     *
     * @return Graph
     */
    public static function createGraph(TranslatorInterface $translator, $domain)
    {
        $categories = self::getCategories($translator, $domain);

        require_once __DIR__ . '/../vendor/autoload.php';

        $graph = new SecurityGraph();

        // VIEW
        $vertices['overview'] = $graph->createVertex('overview');
        $vertices['overview']->setAttribute('title', $translator->trans('Subcollection and media overview', [], $domain));
        $vertices['overview']->setAttribute('description', $translator->trans('Grants access to view the collection\'s media and subcollections.', [], $domain));
        $vertices['overview']->setAttribute('category', $categories['view']);
        $vertices['overview']->setGroup($categories['view']->getId());

        $vertices['download-collection'] = $graph->createVertex('download-collection');
        $vertices['download-collection']->setAttribute('title', $translator->trans('Download whole collection', [], $domain));
        $vertices['download-collection']->setAttribute('description', $translator->trans('Grants access to download the whole collection.', [], $domain));
        $vertices['download-collection']->setAttribute('category', $categories['view']);
        $vertices['download-collection']->setGroup($categories['view']->getId());
        $vertices['download-collection']->createEdgeTo($vertices['overview']);

        $vertices['media-details'] = $graph->createVertex('media-details');
        $vertices['media-details']->setAttribute('title', $translator->trans('Display media details', [], $domain));
        $vertices['media-details']->setAttribute('description', $translator->trans('Grants access to the media details page.', [], $domain));
        $vertices['media-details']->setAttribute('category', $categories['view']);
        $vertices['media-details']->setGroup($categories['view']->getId());
        $vertices['media-details']->createEdgeTo($vertices['overview']);

        $vertices['download-single-media'] = $graph->createVertex('download-single-media');
        $vertices['download-single-media']->setAttribute('title', $translator->trans('Download single media', [], $domain));
        $vertices['download-single-media']->setAttribute('description', $translator->trans('Grants access to download a single medium.', [], $domain));
        $vertices['download-single-media']->setAttribute('category', $categories['view']);
        $vertices['download-single-media']->setGroup($categories['view']->getId());
        $vertices['download-single-media']->createEdgeTo($vertices['media-details']);
        $vertices['download-single-media']->createEdgeTo($vertices['download-collection']);

        // ADD
        $vertices['add-media'] = $graph->createVertex('add-media');
        $vertices['add-media']->setAttribute('title', $translator->trans('Add new media', [], $domain));
        $vertices['add-media']->setAttribute('description', $translator->trans('Grants access to create new media.', [], $domain));
        $vertices['add-media']->setAttribute('category', $categories['add']);
        $vertices['add-media']->setGroup($categories['add']->getId());

        $vertices['add-sub-collections'] = $graph->createVertex('add-sub-collections');
        $vertices['add-sub-collections']->setAttribute('title', $translator->trans('Add new sub-collections', [], $domain));
        $vertices['add-sub-collections']->setAttribute('description', $translator->trans('Grants access to create new collections.', [], $domain));
        $vertices['add-sub-collections']->setAttribute('category', $categories['add']);
        $vertices['add-sub-collections']->setGroup($categories['add']->getId());

        // EDIT
        $vertices['edit-media'] = $graph->createVertex('edit-media');
        $vertices['edit-media']->setAttribute('title', $translator->trans('Edit media', [], $domain));
        $vertices['edit-media']->setAttribute('description', $translator->trans('Grants access to edit media.', [], $domain));
        $vertices['edit-media']->setAttribute('category', $categories['edit']);
        $vertices['edit-media']->setGroup($categories['edit']->getId());
        $vertices['edit-media']->createEdgeTo($vertices['download-single-media']);
        $vertices['edit-media']->createEdgeTo($vertices['add-media']);

        $vertices['edit-collection'] = $graph->createVertex('edit-collection');
        $vertices['edit-collection']->setAttribute('title', $translator->trans('Edit collection', [], $domain));
        $vertices['edit-collection']->setAttribute('description', $translator->trans('Grants access to edit the collection.', [], $domain));
        $vertices['edit-collection']->setAttribute('category', $categories['edit']);
        $vertices['edit-collection']->setGroup($categories['edit']->getId());
        $vertices['edit-collection']->createEdgeTo($vertices['download-collection']);

        $vertices['edit-sub-collections'] = $graph->createVertex('edit-sub-collections');
        $vertices['edit-sub-collections']->setAttribute('title', $translator->trans('Edit sub-collections', [], $domain));
        $vertices['edit-sub-collections']->setAttribute('description', $translator->trans('Grants access to edit sub-collection.', [], $domain));
        $vertices['edit-sub-collections']->setAttribute('category', $categories['edit']);
        $vertices['edit-sub-collections']->setGroup($categories['edit']->getId());
        $vertices['edit-sub-collections']->createEdgeTo($vertices['add-sub-collections']);
        $vertices['edit-sub-collections']->createEdgeTo($vertices['download-collection']);

        // DELETE
        $vertices['delete-media'] = $graph->createVertex('delete-media');
        $vertices['delete-media']->setAttribute('title', $translator->trans('Delete media', [], $domain));
        $vertices['delete-media']->setAttribute('description', $translator->trans('Grants access to delete media.', [], $domain));
        $vertices['delete-media']->setAttribute('category', $categories['delete']);
        $vertices['delete-media']->setGroup($categories['delete']->getId());
        $vertices['delete-media']->createEdgeTo($vertices['edit-media']);

        $vertices['delete-sub-collections'] = $graph->createVertex('delete-sub-collections');
        $vertices['delete-sub-collections']->setAttribute('title', $translator->trans('Delete sub-collections', [], $domain));
        $vertices['delete-sub-collections']->setAttribute('description', $translator->trans('Grants access to delete sub-collections.', [], $domain));
        $vertices['delete-sub-collections']->setAttribute('category', $categories['delete']);
        $vertices['delete-sub-collections']->setGroup($categories['delete']->getId());
        $vertices['delete-sub-collections']->createEdgeTo($vertices['edit-sub-collections']);

        $vertices['delete-collection'] = $graph->createVertex('delete-collection');
        $vertices['delete-collection']->setAttribute('title', $translator->trans('Delete collection', [], $domain));
        $vertices['delete-collection']->setAttribute('description', $translator->trans('Grants access to delete the collection.', [], $domain));
        $vertices['delete-collection']->setAttribute('category', $categories['delete']);
        $vertices['delete-collection']->setGroup($categories['delete']->getId());
        $vertices['delete-collection']->createEdgeTo($vertices['delete-media']);
        $vertices['delete-collection']->createEdgeTo($vertices['delete-sub-collections']);
        $vertices['delete-collection']->createEdgeTo($vertices['edit-collection']);

        // PERMISSIONS
        $vertices['widen-permissions'] = $graph->createVertex('widen-permissions');
        $vertices['widen-permissions']->setAttribute('title', $translator->trans('Widen permissions', [], $domain));
        $vertices['widen-permissions']->setAttribute('description', $translator->trans('Allows to widen the permissions.', [], $domain));
        $vertices['widen-permissions']->setAttribute('category', $categories['permission']);
        $vertices['widen-permissions']->setGroup($categories['permission']->getId());
        $vertices['widen-permissions']->createEdgeTo($vertices['delete-collection']);

        return $graph;
    }

    /**
     * @param TranslatorInterface $translator
     * @param $domain
     *
     * @return SecurityCategory[]
     */
    public static function getCategories(TranslatorInterface $translator, $domain)
    {
        return [
            'view' => new SecurityCategory(1, $translator->trans('View', [], $domain)),
            'add' => new SecurityCategory(2, $translator->trans('Add', [], $domain)),
            'edit' => new SecurityCategory(3, $translator->trans('Edit', [], $domain)),
            'delete' => new SecurityCategory(4, $translator->trans('Delete', [], $domain)),
            'permission' => new SecurityCategory(5, $translator->trans('Permissions', [], $domain)),
        ];
    }
}
