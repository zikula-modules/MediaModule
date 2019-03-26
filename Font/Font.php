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

namespace Cmfcmf\Module\MediaModule\Font;

/**
 * Convenience class to represent a font.
 */
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
     * @param string      $id             the unique font id
     * @param string      $title          the font title
     * @param string      $path           the font path
     * @param string|null $googleFontName the name of the corresponding Google font or null if it
     *                                    doesn't exist
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
