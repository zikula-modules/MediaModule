<?php

use Cmfcmf\Module\MediaModule\Security\SecurityTree;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Translator.php';

$graph = SecurityTree::createGraph(new Translator(), '');
$categories = SecurityTree::getCategories(new Translator(), '');

/** @var \Fhaculty\Graph\Vertex $vertex */
foreach ($graph->getVertices() as $vertex) {
    $vertex->setAttribute('graphviz.label', $vertex->getAttribute('title'));
}

foreach ($graph->getEdges() as $edge) {
    if ($edge->getAttribute('edgeType') == SecurityTree::EDGE_TYPE_INCLUDED_PERMISSIONS) {
        $edge->setAttribute('graphviz.label',  '<includes>');
        $edge->setAttribute('graphviz.color',  'blue');
    } else if ($edge->getAttribute('edgeType') == SecurityTree::EDGE_TYPE_PERMISSIONS_IF_DEFINED_IN_PARENT) {
        $edge->setAttribute('graphviz.label',  "<substitutes if\ndefined in parent>");
        $edge->setAttribute('graphviz.color',  'green');
    }
}

$graphviz = new \Graphp\GraphViz\GraphViz();
$graphviz->display($graph);
