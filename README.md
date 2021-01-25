Zikula 2.0.11+ media module
===========================

[![StyleCI](https://styleci.io/repos/43518681/shield)](https://styleci.io/repos/43518681) 
[![Build Status](https://travis-ci.org/zikula-modules/MediaModule.svg?branch=main)](https://travis-ci.org/zikula-modules/MediaModule) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zikula-modules/MediaModule/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/zikula-modules/MediaModule/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/zikula-modules/MediaModule/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/zikula-modules/MediaModule/?branch=main)
[![MIT License](https://img.shields.io/github/license/zikula-modules/MediaModule.svg)]()

## Installation 
1. **Download the module from https://github.com/zikula-modules/MediaModule/releases/latest.**
2. Install to `modules/cmfcmf/media-module` (but other locations should work aswell!).

If you want to test the current version (potentially unstable!), download it from here: https://github.com/zikula-modules/MediaModule/raw/dev-builds/MediaModule.zip

**Downloading directly from main would require you to execute `composer install --no-dev`.**

## Information
The module supports many media types natively (images, plaintext, MarkDown, videos, audio, pdf, …)
and further types can be added with ease! 

Not only can you upload media, you can also search for and embed all kinds of media from the web!
Here are some: Tweets, YouTube videos, Music, Flickr images, …

You can create text- and image-based watermarks to watermark all uploaded images.
The watermarks scale automatically and can be exchanged at any time. 
The original images are preserved. 

Rendered MarkDown files? Syntax-highlighted source code? Automatic updates?
Thumbnail generation? Works on mobile? YES. All of this. Read on or download the module already! 

## Known issues
- **You MAY NOT allow untrusted users to upload files. It is currently NOT SAFE to do so.**
- You must not create collections with any of the following tittles: 
*f*, *media*, *settings*, *admin*, *hooks*, *licenses*, *watermarks*. 
If you do so, it's going to break URLs.
- The Flickr MediaType is currently disabled due to legal questions
- Even though files are watermarked, they are still available non-watermarked. Users could try to guess
  the URL and access the un-watermarked files.

## Talk to me!
If you find a bug or have problems, please [create an issue](https://github.com/zikula-modules/MediaModule/issues/new)!

## Extracting translations
Add `require_once __DIR__ . '/../modules/cmfcmf/media-module/vendor/autoload.php';` to `src/app/autoload.php` and then run

`php -dmemory_limit=2G bin/console translation:extract en --bundle=CmfcmfMediaModule --enable-extractor=jms_i18n_routing --output-format=po --exclude-dir=vendor`

## License and module development

The code is MIT licensed, see the `License.md` file for further information.
