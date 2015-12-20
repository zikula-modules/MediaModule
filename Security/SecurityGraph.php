<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\VerticesMap;
use Fhaculty\Graph\Vertex;

class SecurityGraph extends Graph
{
    public function getVerticesByCategory(SecurityCategory $securityCategory)
    {
        return $this->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($securityCategory) {
            return $vertex->getGroup() == $securityCategory->getId();
        });
    }

    public function getChildrenOfVertex(Vertex $vertex)
    {
        $children = new VerticesMap();
        /** @var Directed $edge */
        foreach ($vertex->getEdgesOut() as $edge) {
            $end = $edge->getVertexEnd();
            $children = VerticesMap::factory($children->getMap() + [$end] + $this->getChildrenOfVertex($end)->getMap());
        }

        return VerticesMap::factory($children);
    }
}