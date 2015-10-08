<?php

namespace Cmfcmf\Module\MediaModule\Font;

interface FontInterface
{
    /**
     * The font title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * The font id. Must be unique!
     *
     * @return string
     */
    public function getId();

    /**
     * The path to the .ttf file.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the font name of the corresponding Google font. Returns null if it doesn't exist.
     *
     * @return null|string
     */
    public function getGoogleFontName();
}
