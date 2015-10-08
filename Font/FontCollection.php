<?php

namespace Cmfcmf\Module\MediaModule\Font;

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

    private $loaded;

    public function __construct()
    {
        $this->fonts = [];
        $this->fontLoaders = [];
        $this->loaded = false;
    }

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

        return $this->fonts[$id];
    }

    /**
     * Returns the Google Font URL.
     *
     * @return string
     */
    public function getFontUrl()
    {
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
    }

    /**
     * Returns an array of forms to use with Symfony forms.
     *
     * @return array
     */
    public function getFontsForForm()
    {
        return array_map(function (FontInterface $font) {
            return $font->getTitle();
        }, $this->getFonts());
    }
}
