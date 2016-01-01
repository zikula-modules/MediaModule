<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Tests\Font;

use Cmfcmf\Module\MediaModule\Font\Font;

class FontTest extends \PHPUnit_Framework_TestCase
{
    public function testWithGoogleFontName()
    {
        $font = new Font('theID', 'theTitle', 'thePath', 'theGoogleThing');

        $this->assertEquals('theID', $font->getId());
        $this->assertEquals('theTitle', $font->getTitle());
        $this->assertEquals('thePath', $font->getPath());
        $this->assertEquals('theGoogleThing', $font->getGoogleFontName());
    }

    public function testWithoutGoogleFontName()
    {
        $font = new Font('theID', 'theTitle', 'thePath', null);

        $this->assertEquals('theID', $font->getId());
        $this->assertEquals('theTitle', $font->getTitle());
        $this->assertEquals('thePath', $font->getPath());
        $this->assertEquals(null, $font->getGoogleFontName());
    }
}
