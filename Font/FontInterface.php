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
 * Represents a font.
 */
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
