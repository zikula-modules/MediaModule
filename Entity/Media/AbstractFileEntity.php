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
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Uploadable\Uploadable;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @ORM\Entity(repositoryClass="Cmfcmf\Module\MediaModule\Entity\Media\Repository\MediaRepository")
 * @Gedmo\Uploadable(pathMethod="getPathToUploadTo", callback="onNewFile", filenameGenerator="SHA1", appendNumber=true)
 */
abstract class AbstractFileEntity extends AbstractMediaEntity implements Uploadable
{
    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileName
     *
     * @var string
     */
    protected $fileName;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileMimeType
     *
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     *
     * @var int
     */
    protected $fileSize;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $downloadAllowed;

    /**
     * @param RequestStack $requestStack
     * @param string       $dataDirectory
     */
    public function __construct(RequestStack $requestStack, $dataDirectory = '')
    {
        parent::__construct($requestStack, $dataDirectory);

        $this->downloadAllowed = true;
    }

    public function getPathToUploadTo($defaultPath)
    {
        unset($defaultPath);

        return str_replace('public/', '', $this->dataDirectory) . '/cmfcmf-media-module/media';
    }

    public function getPath()
    {
        return $this->getPathToUploadTo(null) . '/' . $this->fileName;
    }

    public function getUrl()
    {
        return $this->getBaseUri() . '/' . $this->getPath();
    }

    public function getBeautifiedFileName()
    {
        // Found at http://stackoverflow.com/a/2021729
        // written by Seab Vieira http://stackoverflow.com/users/135978/sean-vieira

        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;:[]().
        $filename = mb_ereg_replace("([^\\w\\s\\d\\-_~,;:\\[\\]\\(\\).])", '', $this->getTitle());
        // Remove any runs of periods (thanks falstro!)
        $filename = mb_ereg_replace("([\\.]{2,})", '', $filename);
        $extension = pathinfo($this->getFileName(), PATHINFO_EXTENSION);

        if (mb_substr($filename, -mb_strlen($extension) - 1) === ".${extension}") {
            $filename = mb_substr($filename, 0, -mb_strlen($extension) - 1);
        }

        return "${filename}.${extension}";
    }

    public function onNewFile(array $info)
    {
        // Do nothing for now.

        // fileName: The filename.
        // fileExtension: The extension of the file (including the dot). Example: .jpg
        // fileWithoutExt: The filename without the extension.
        // filePath: The file path. Example: /my/path/filename.jpg
        // fileMimeType: The mime-type of the file. Example: text/plain.
        // fileSize: Size of the file in bytes. Example: 140000.
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     *
     * @return AbstractFileEntity
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param mixed $mimeType
     *
     * @return AbstractFileEntity
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param mixed $fileSize
     *
     * @return AbstractFileEntity
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * @param bool $downloadAllowed
     *
     * @return AbstractFileEntity
     */
    public function setDownloadAllowed($downloadAllowed)
    {
        $this->downloadAllowed = $downloadAllowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDownloadAllowed()
    {
        return $this->downloadAllowed;
    }
}
