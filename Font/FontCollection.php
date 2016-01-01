<?php

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
 * A collection of fonts.
 */
class FontCollection
{
    /**
     * @var FontLoaderInterface[]
     */
    private $fontLoaders;

    /**
     * @var FontInterface[]
     */
    private $fonts;

    /**
     * @var bool Whether or not the fonts have already been loaded.
     */
    private $loaded;

    public function __construct()
    {
        $this->fonts = [];
        $this->fontLoaders = [];
        $this->loaded = false;
    }

    /**
     * Adds a font loader to the collection.
     *
     * @param FontLoaderInterface $fontLoader
     */
    public function addFontLoader(FontLoaderInterface $fontLoader)
    {
        if ($this->loaded) {
            throw new \RuntimeException('You cannot add another font loader once the fonts are loaded!');
        }
        $this->fontLoaders[] = $fontLoader;
    }

    /**
     * Returns all available fonts, indexed by font id.
     *
     * @return FontInterface[]
     */
    public function getFonts()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return $this->fonts;
    }

    /**
     * Returns an array of forms to use with Symfony forms.
     *
     * @return array
     */
    public function getFontsForForm()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return array_map(function (FontInterface $font) {
            return $font->getTitle();
        }, $this->getFonts());
    }

    /**
     * Returns a font by id.
     *
     * @param string $id The font id.
     *
     * @return FontInterface
     */
    public function getFontById($id)
    {
        if (!$this->loaded) {
            $this->load();
        }
        if (!isset($this->fonts[$id])) {
            throw new \InvalidArgumentException('The font with the requested ID does not exist.');
        }

        return $this->fonts[$id];
    }

    /**
     * Returns the Google Font URL.
     *
     * @return string
     */
    public function getFontUrl()
    {
        if (!$this->loaded) {
            $this->load();
        }

        $fontUrl = 'https://fonts.googleapis.com/css?family=';
        foreach ($this->getFonts() as $font) {
            if ($font->getGoogleFontName() !== null) {
                $fontUrl .= str_replace('_', '+', $font->getGoogleFontName()) . '|';
            }
        }

        return $fontUrl;
    }

    /**
     * Loads all fonts from the loaders.
     */
    private function load()
    {
        foreach ($this->fontLoaders as $loader) {
            foreach ($loader->loadFonts() as $font) {
                $this->fonts[$font->getId()] = $font;
            }
        }

        $this->loaded = true;
    }
}
