<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionCategory;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Set\VerticesMap;
use Fhaculty\Graph\Vertex;

class SecurityGraph extends Graph
{
    /**
     * @param CollectionPermissionCategory $securityCategory
     *
     * @return Vertices
     */
    public function getVerticesByCategory(CollectionPermissionCategory $securityCategory)
    {
        return $this->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($securityCategory) {
            return $vertex->getGroup() == $securityCategory->getId();
        });
    }

    /**
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getRequiredVertices(Vertex $vertex)
    {
        $children = new VerticesMap();
        /** @var Directed $edge */
        foreach ($vertex->getEdgesOut() as $edge) {
            if ($edge->getAttribute('edgeType') == CollectionPermissionSecurityTree::EDGE_TYPE_REQUIRES) {
                $end = $edge->getVertexEnd();
                $children = VerticesMap::factory(
                    $children->getMap() + [$end] + $this->getRequiredVertices($end)->getMap()
                );
            }
        }

        return VerticesMap::factory($children);
    }

    /**
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getConflictedVertices(Vertex $vertex)
    {
        return Vertices::factory(array_map(function (Base $edge) use ($vertex) {
            return $edge->getVertexToFrom($vertex);
        }, $vertex->getEdgesOut()->getEdgesMatch(function (Base $edge) {
            return $edge->getAttribute('edgeType') == CollectionPermissionSecurityTree::EDGE_TYPE_CONFLICTS;
        })->getVector()));
    }

    /**
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getVerticesRequiring(Vertex $vertex)
    {
        $parents = new VerticesMap();
        /** @var Directed $edge */
        foreach ($vertex->getEdgesIn() as $edge) {
            if ($edge->getAttribute('edgeType') == CollectionPermissionSecurityTree::EDGE_TYPE_REQUIRES) {
                $start = $edge->getVertexStart();
                $parents = VerticesMap::factory(
                    $parents->getMap() + [$start] + $this->getVerticesRequiring($start)->getMap()
                );
            }
        }

        return VerticesMap::factory($parents);
    }
}
