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
    $edge->setAttribute('graphviz.label', '<includes>');
    $edge->setAttribute('graphviz.color', 'blue');
}

$graphviz = new \Graphp\GraphViz\GraphViz();
$graphviz->display($graph);
