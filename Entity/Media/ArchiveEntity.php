<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ArchiveEntity extends AbstractFileEntity
{
    public function onNewFile(array $info)
    {
        parent::onNewFile($info);

        $files = [];
        switch ($info['fileMimeType']) {
            case 'application/x-gzip':
            case 'application/x-tar':
            case 'application/x-gtar':
                try {
                    $tar = new \PharData($info['filePath']);
                } catch (\UnexpectedValueException $e) {
                    break;
                }
                $this->setNumberOfFiles($tar->count());

                $i = 0;
                foreach ($tar as $file) {
                    $files[] = $file;
                    if ($i >= 1000) {
                        break;
                    }
                    $i++;
                }
                break;
            case 'application/x-zip-compressed':
            case 'application/zip':
            case 'multipart/x-zip':
                $zip = new \ZipArchive();
                $zip->open($info['filePath']);
                $this->setNumberOfFiles($zip->numFiles);
                for ($i = 0; $i < $zip->numFiles && $i < 1000; $i++) {
                    $file = $zip->statIndex($i);
                    $files[] = $file['name'];
                }
                break;
            default:
                throw new \LogicException();
        }

        $this->extraData['files'] = $files;
    }

    public function getNumberOfFiles()
    {
        return isset($this->extraData['numberOfFiles']) ? $this->extraData['numberOfFiles'] : false;
    }

    public function setNumberOfFiles($numberOfFiles)
    {
        $this->extraData['numberOfFiles'] = $numberOfFiles;
    }
}
