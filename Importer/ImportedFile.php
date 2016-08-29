<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Importer;

use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImportedFile implements FileInfoInterface
{
    /**
     * @var File
     */
    private $file;

    private function __construct()
    {
    }

    public static function fromFile(File $file)
    {
        $importedFile = new static();
        $importedFile->file = $file;

        return $importedFile;
    }

    public function getTmpName()
    {
        return $this->file->getPathname();
    }

    public function getName()
    {
        return $this->file->getFilename();
    }

    public function getSize()
    {
        return $this->file->getSize();
    }

    public function getType()
    {
        return $this->file->getMimeType();
    }

    public function getError()
    {
        return 0;
    }

    /**
     * This method must return true if the file is coming from $_FILES, or false instead.
     *
     * @return bool
     */
    public function isUploadedFile()
    {
        return false;
    }
}
