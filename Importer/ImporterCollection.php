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
