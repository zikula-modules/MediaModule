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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ArchiveEntity extends AbstractFileEntity
{
    public function onNewFile(array $info): void
    {
        parent::onNewFile($info);

        $limit = 1000;
        $files = [];
        switch ($info['fileMimeType']) {
            case 'application/x-gzip':
            case 'application/x-tar':
            case 'application/x-gtar':
                if (!class_exists('PharData')) {
                    break;
                }
                try {
                    $tar = new \PharData($info['filePath']);
                } catch (\UnexpectedValueException $e) {
                    break;
                }
                if (!$tar->decompressFiles()) {
                    break;
                }
                $this->setNumberOfFiles($tar->count());

                $i = 0;
                /** @var \PharFileInfo $file */
                foreach ($tar as $file) {
                    $files[] = $file->getFilename();
                    if ($file->isDir()) {
                        $dir = new \PharData($file->getPathname());
                        foreach ($dir as $child) {
                            $files[] = $file->getFilename() . '/' . $child->getFilename();
                            if ($child->isDir()) {
                                $subdir = new \PharData($child->getPathname());
                                foreach ($subdir as $grandchild) {
                                    $files[] = $file->getFilename() . '/' . $child->getFilename() . '/' . $grandchild->getFilename();
                                    if ($i >= $limit) {
                                        break;
                                    }
                                    $i++;
                                }
                            }
                            if ($i >= $limit) {
                                break;
                            }
                            $i++;
                        }
                    }
                    if ($i >= $limit) {
                        break;
                    }
                    $i++;
                }
                break;
            case 'application/x-zip-compressed':
            case 'application/zip':
            case 'multipart/x-zip':
                if (!class_exists('ZipArchive')) {
                    break;
                }
                $zip = new \ZipArchive();
                $zip->open($info['filePath']);
                $this->setNumberOfFiles($zip->numFiles);
                for ($i = 0; $i < $zip->numFiles && $i < $limit; $i++) {
                    $file = $zip->statIndex($i);
                    $files[] = $file['name'];
                }
                break;
            case 'application/x-rar-compressed':
            case 'application/rar':
                if (!class_exists('RarArchive')) {
                    break;
                }
                $rar = \RarArchive::open($filePath);
                if (!$rar) {
                    break;
                }

                $entries = $rar->getEntries();
                $this->setNumberOfFiles(count($entries));

                $i = 0;
                foreach ($entries as $entry) {
                    $files[] = $entry->getName();
                    if ($i >= $limit) {
                        break;
                    }
                    $i++;
                }
                $rar->close();
            default:
                throw new \LogicException();
        }

        $this->extraData['files'] = $files;
    }

    public function getNumberOfFiles(): ?int
    {
        return $this->extraData['numberOfFiles'] ?? false;
    }

    public function setNumberOfFiles(int $numberOfFiles): self
    {
        $this->extraData['numberOfFiles'] = $numberOfFiles;

        return $this;
    }
}
