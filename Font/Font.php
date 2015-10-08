<?php

namespace Cmfcmf\Module\MediaModule\Font;

class Font implements FontInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $path;

    /**
     * @var null|string
     */
    private $googleFontName;

    /**
     * @param string      $id             The unique font id.
     * @param string      $title          The font title.
     * @param string      $path           The font path.
     * @param string|null $googleFontName The name of the corresponding Google font or null if it doesn't exist.
     */
    public function __construct($id, $title, $path, $googleFontName)
    {
        $this->id = $id;
        $this->title = $title;
        $this->path = $path;
        $this->googleFontName = $googleFontName;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleFontName()
    {
        return $this->googleFontName;
    }
}
