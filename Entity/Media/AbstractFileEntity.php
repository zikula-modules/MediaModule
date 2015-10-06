<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Uploadable\Uploadable;

/**
 * @ORM\Entity()
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
     * @var bool
     */
    protected $downloadAllowed;

    public function __construct()
    {
        parent::__construct();

        $this->downloadAllowed = true;
    }

    public function getPathToUploadTo($defaultPath)
    {
        unset($defaultPath);

        return \FileUtil::getDataDirectory() . '/cmfcmf-media-module/media';
    }

    public function getPath()
    {
        return $this->getPathToUploadTo(null) . '/' . $this->fileName;
    }

    public function getUrl()
    {
        return \System::getBaseUri() . '/' . $this->getPath();
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

        if (substr($filename, -strlen($extension) - 1) == ".$extension") {
            $filename = substr($filename, 0, -strlen($extension) - 1);
        }

        return "$filename.$extension";
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
     * @return AbstractFileEntity
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    /**
     * @param boolean $downloadAllowed
     * @return AbstractFileEntity
     */
    public function setDownloadAllowed($downloadAllowed)
    {
        $this->downloadAllowed = $downloadAllowed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDownloadAllowed()
    {
        return $this->downloadAllowed;
    }
}
