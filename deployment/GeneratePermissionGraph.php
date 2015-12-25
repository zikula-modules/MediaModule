<?php

use Cmfcmf\Module\MediaModule\Security\SecurityTree;
use Fhaculty\Graph\Edge\Base;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Translator.php';

$graph = SecurityTree::createGraph(new Translator(), '');
$categories = SecurityTree::getCategories(new Translator(), '');

/** @var \Fhaculty\Graph\Vertex $vertex */
foreach ($graph->getVertices() as $vertex) {
    $vertex->setAttribute('graphviz.label', $vertex->getAttribute('title'));
}

/** @var Base $edge */
foreach ($graph->getEdges() as $edge) {
    if ($edge->getAttribute('edgeType') == SecurityTree::EDGE_TYPE_REQUIRES) {
        $edge->setAttribute('graphviz.label', '<requires>');
        $edge->setAttribute('graphviz.color', 'blue');
    } else if ($edge->getAttribute('edgeType') == SecurityTree::EDGE_TYPE_CONFLICTS) {
        $edge->setAttribute('graphviz.label', '<conflicts>');
        $edge->setAttribute('graphviz.color', 'red');
    }
}

$graphviz = new \Graphp\GraphViz\GraphViz();
rename($graphviz->createImageFile($graph), __DIR__ . '/complete-graph.png');

foreach ($graph->getEdges()->getEdgesMatch(function (Base $edge) {
    return $edge->getAttribute('edgeType') == SecurityTree::EDGE_TYPE_CONFLICTS;
}) as $edge) {
    $graph->removeEdge($edge);
}


$graphviz = new \Graphp\GraphViz\GraphViz();
rename($graphviz->createImageFile($graph), __DIR__ . '/require-graph.png');
