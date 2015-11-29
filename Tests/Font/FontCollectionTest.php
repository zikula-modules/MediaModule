<?php

namespace Cmfcmf\Module\MediaModule\Tests\Font;

use Cmfcmf\Module\MediaModule\Font\FontCollection;

class FontCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testNoFontsIfNoLoaderProvided()
    {
        $fontCollection = new FontCollection();

        $this->assertEquals([], $fontCollection->getFonts());
        $this->assertEquals([], $fontCollection->getFontsForForm());
    }

    public function testIfExceptionWhenAddingLoaderAfterFontsAreLoaded()
    {
        $this->setExpectedException('RuntimeException');

        $fontCollection = new FontCollection();
        $fontCollection->getFonts();

        $fontLoader = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontLoaderInterface')->getMock();

        $fontCollection->addFontLoader($fontLoader);
    }

    public function testFontsForForm()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $this->assertEquals(['fontID' => 'fontTitle'], $fontCollection->getFontsForForm());
    }

    public function testGetFontUrlWithOneFont()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $this->assertEquals('https://fonts.googleapis.com/css?family=googleName|', $fontCollection->getFontUrl());
    }

    public function testGetFontUrlWithOneFontButNoGoogleName()
    {
        $fontCollection = new FontCollection();

        $font = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontInterface')->getMock();
        $font
            ->expects($this->any())
            ->method('getId')
            ->willReturn('fontID')
        ;
        $font
            ->expects($this->any())
            ->method('getGoogleFontName')
            ->willReturn(null)
        ;
        $font
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn('fontTitle')
        ;

        $fontLoader = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontLoaderInterface')->getMock();
        $fontLoader
            ->expects($this->once())
            ->method('loadFonts')
            ->willReturn([$font])
        ;

        $fontCollection->addFontLoader($fontLoader);

        $this->assertEquals('https://fonts.googleapis.com/css?family=', $fontCollection->getFontUrl());
    }

    public function testGetFontUrlWithMultipleFonts()
    {
        $fontCollection = new FontCollection();

        $font1 = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontInterface')->getMock();
        $font1
            ->expects($this->any())
            ->method('getId')
            ->willReturn('font1')
        ;
        $font1
            ->expects($this->any())
            ->method('getGoogleFontName')
            ->willReturn('font_1')
        ;
        $font1
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn('font1')
        ;

        $font2 = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontInterface')->getMock();
        $font2
            ->expects($this->any())
            ->method('getId')
            ->willReturn('font2')
        ;
        $font2
            ->expects($this->any())
            ->method('getGoogleFontName')
            ->willReturn(null)
        ;
        $font2
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn('fontTitle')
        ;

        $font3 = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontInterface')->getMock();
        $font3
            ->expects($this->any())
            ->method('getId')
            ->willReturn('font3')
        ;
        $font3
            ->expects($this->any())
            ->method('getGoogleFontName')
            ->willReturn('font3')
        ;
        $font3
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn('font3')
        ;

        $fontLoader = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontLoaderInterface')->getMock();
        $fontLoader
            ->expects($this->once())
            ->method('loadFonts')
            ->willReturn([$font1, $font2, $font3])
        ;

        $fontCollection->addFontLoader($fontLoader);

        $this->assertEquals('https://fonts.googleapis.com/css?family=font+1|font3|', $fontCollection->getFontUrl());
    }

    public function testExceptionIfFontDoesntExist()
    {
        $this->setExpectedException('InvalidArgumentException');

        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $fontCollection->getFontById('IDoNotExist');
    }

    public function testFontsLoadedIfCallingGetFonts()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $this->assertCount(1, $fontCollection->getFonts());
    }

    public function testFontsLoadedIfCallingGetFontsForForm()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $this->assertCount(1, $fontCollection->getFontsForForm());
    }

    public function testFontsLoadedIfCallingGetFontById()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        // Will throw an exception if not loaded
        $fontCollection->getFontById('fontID');
    }

    public function testFontsLoadedIfCallingGetFontUrl()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        $this->assertContains('googleName', $fontCollection->getFontUrl());
    }

    public function testLoaderNotLoadingTwiceIfCallingGetFonts()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        // This should load the fonts.
        $fontCollection->getFonts();

        // This shouldn't load them again.
        $fontCollection->getFonts();
    }

    public function testLoaderNotLoadingTwiceIfCallingGetFontsForForm()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        // This should load the fonts.
        $fontCollection->getFontsForForm();

        // This shouldn't load them again.
        $fontCollection->getFontsForForm();
    }

    public function testLoaderNotLoadingTwiceIfCallingGetFontUrl()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        // This should load the fonts.
        $fontCollection->getFontUrl();

        // This shouldn't load them again.
        $fontCollection->getFontUrl();
    }

    public function testLoaderNotLoadingTwiceIfCallingGetFontById()
    {
        $fontCollection = $this->getFontCollectionWithLoaderAndOneFont();

        // This should load the fonts.
        $fontCollection->getFontById('fontID');

        // This shouldn't load them again.
        $fontCollection->getFontById('fontID');
    }

    private function getFontCollectionWithLoaderAndOneFont()
    {
        $fontCollection = new FontCollection();

        $font = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontInterface')->getMock();
        $font
            ->expects($this->any())
            ->method('getId')
            ->willReturn('fontID')
        ;
        $font
            ->expects($this->any())
            ->method('getGoogleFontName')
            ->willReturn('googleName')
        ;
        $font
            ->expects($this->any())
            ->method('getTitle')
            ->willReturn('fontTitle')
        ;

        $fontLoader = $this->getMockBuilder('Cmfcmf\Module\MediaModule\Font\FontLoaderInterface')->getMock();
        $fontLoader
            ->expects($this->once())
            ->method('loadFonts')
            ->willReturn([$font])
        ;

        $fontCollection->addFontLoader($fontLoader);

        return $fontCollection;
    }
}