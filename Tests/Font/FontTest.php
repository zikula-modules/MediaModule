<?php

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
