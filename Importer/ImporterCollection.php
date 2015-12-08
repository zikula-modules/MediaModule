<?php

namespace Cmfcmf\Module\MediaModule\Importer;

class ImporterCollection
{
    /**
     * @var ImporterInterface[]
     */
    private $importers;

    public function __construct()
    {
        $this->importers = [];
    }

    /**
     * Adds an importer to the collection.
     *
     * @param ImporterInterface $importer
     */
    public function addImporter(ImporterInterface $importer)
    {
        $this->importers[$importer->getId()] = $importer;
    }

    /**
     * Returns all importers.
     *
     * @return array|ImporterInterface[]
     */
    public function getImporters()
    {
        return $this->importers;
    }

    public function getImporter($id)
    {
        if (!isset($this->importers[$id])) {
            throw new \InvalidArgumentException('An importer with the specified id does not exist!');
        }

        return $this->importers[$id];
    }

    public function hasImporter($id)
    {
        return isset($this->importers[$id]);
    }
}
