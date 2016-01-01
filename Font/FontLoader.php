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

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FontLoader implements FontLoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function loadFonts()
    {
        $fonts = [];
        $finder = Finder::create()
            ->files()
            ->name('*.ttf')
            ->in(__DIR__ . '/../Resources/fonts')
        ;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fontName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $title = str_replace('_', ' ', $fontName);

            $fonts[] = new Font("cmfcmfmediamodule:$fontName", $title, $file->getPathname(), $fontName);
        }

        return $fonts;
    }
}
