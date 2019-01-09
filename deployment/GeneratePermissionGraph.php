<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Fhaculty\Graph\Edge\Base;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Translator.php';

$graph = CollectionPermissionSecurityTree::createGraph(new Translator());
$categories = CollectionPermissionSecurityTree::getCategories(new Translator());

/** @var \Fhaculty\Graph\Vertex $vertex */
foreach ($graph->getVertices() as $vertex) {
    $vertex->setAttribute('graphviz.label', $vertex->getAttribute('title'));
}

/** @var Base $edge */
foreach ($graph->getEdges() as $edge) {
    if (CollectionPermissionSecurityTree::EDGE_TYPE_REQUIRES == $edge->getAttribute('edgeType')) {
        $edge->setAttribute('graphviz.label', '<requires>');
        $edge->setAttribute('graphviz.color', 'blue');
    } elseif (CollectionPermissionSecurityTree::EDGE_TYPE_CONFLICTS == $edge->getAttribute('edgeType')) {
        $edge->setAttribute('graphviz.label', '<conflicts>');
        $edge->setAttribute('graphviz.color', 'red');
    }
}

$graphviz = new \Graphp\GraphViz\GraphViz();
rename($graphviz->createImageFile($graph), __DIR__ . '/complete-graph.png');

foreach ($graph->getEdges()->getEdgesMatch(function (Base $edge) {
    return CollectionPermissionSecurityTree::EDGE_TYPE_CONFLICTS == $edge->getAttribute('edgeType');
}) as $edge) {
    $graph->removeEdge($edge);
}

$graphviz = new \Graphp\GraphViz\GraphViz();
rename($graphviz->createImageFile($graph), __DIR__ . '/require-graph.png');
