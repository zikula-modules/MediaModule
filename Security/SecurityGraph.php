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

namespace Cmfcmf\Module\MediaModule\Security;

use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionCategory;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Set\VerticesMap;
use Fhaculty\Graph\Vertex;

/**
 * Represents the graph of permissions.
 * Extends the Fhaculty Graph class and adds some convenience methods.
 */
class SecurityGraph extends Graph
{
    /**
     * Returns all vertices in the specified category.
     *
     * @param CollectionPermissionCategory $securityCategory
     *
     * @return Vertices
     */
    public function getVerticesByCategory(CollectionPermissionCategory $securityCategory)
    {
        return $this->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($securityCategory) {
            return $vertex->getGroup() === $securityCategory->getId();
        });
    }

    /**
     * Returns all vertices required by the given vertex.
     *
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getRequiredVertices(Vertex $vertex)
    {
        $children = new VerticesMap();
        /** @var Directed $edge */
        foreach ($vertex->getEdgesOut() as $edge) {
            if (CollectionPermissionSecurityTree::EDGE_TYPE_REQUIRES === $edge->getAttribute('edgeType')) {
                $end = $edge->getVertexEnd();
                $children = VerticesMap::factory(
                    $children->getMap() + [$end] + $this->getRequiredVertices($end)->getMap()
                );
            }
        }

        return VerticesMap::factory($children);
    }

    /**
     * Returns all vertices conflicting with the given vertex.
     *
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getConflictedVertices(Vertex $vertex)
    {
        return Vertices::factory(array_map(function (Base $edge) use ($vertex) {
            return $edge->getVertexToFrom($vertex);
        }, $vertex->getEdgesOut()->getEdgesMatch(function (Base $edge) {
            return CollectionPermissionSecurityTree::EDGE_TYPE_CONFLICTS === $edge->getAttribute('edgeType');
        })->getVector()));
    }

    /**
     * Return all vertices requiring the given vertex.
     *
     * @param Vertex $vertex
     *
     * @return Vertices
     */
    public function getVerticesRequiring(Vertex $vertex)
    {
        $parents = new VerticesMap();
        /** @var Directed $edge */
        foreach ($vertex->getEdgesIn() as $edge) {
            if (CollectionPermissionSecurityTree::EDGE_TYPE_REQUIRES === $edge->getAttribute('edgeType')) {
                $start = $edge->getVertexStart();
                $parents = VerticesMap::factory(
                    $parents->getMap() + [$start] + $this->getVerticesRequiring($start)->getMap()
                );
            }
        }

        return VerticesMap::factory($parents);
    }
}
